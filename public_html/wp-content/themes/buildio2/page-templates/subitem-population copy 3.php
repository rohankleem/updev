<?php
/* Template Name: Webhook Monday Subitem Populate 2 */

require_once __DIR__ . '/../../../../wp-load.php';

// Monday.com API Token
define('MONDAY_API_TOKEN', $_ENV['MONDAY_API_TOKEN']);

// Main Webhook Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = file_get_contents('php://input');
    $payload = json_decode($payload, true);

    handle_webhook($payload);
}

// Function to make a cURL request
function monday_curl_request($query) {
    $token = MONDAY_API_TOKEN;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.monday.com/v2");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    write_to_log("HTTP Code: $http_code");
    write_to_log("Response: $response");
    if ($error) write_to_log("cURL Error: $error");

    return json_decode($response, true);
}

// Logging Function
function write_to_log($message) {
    $log_file = WP_CONTENT_DIR . '/themes/buildio2/custom-logs/monday-webhook.log';
    
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Fetch column IDs for a board
function fetch_column_ids($boardId) {
    write_to_log("[INFO] Fetching column IDs for board ID: $boardId");
    $query = json_encode([
        'query' => "query {
            boards(ids: {$boardId}) {
                columns {
                    id
                    title
                }
            }
        }"
    ]);
    $response = monday_curl_request($query);

    if (empty($response['data']['boards'][0]['columns'])) {
        write_to_log("[ERROR] No columns found for board ID: $boardId");
        return null;
    }

    $columns = $response['data']['boards'][0]['columns'];
    $columnMap = [];

    // Map column titles to IDs
    foreach ($columns as $column) {
        $columnMap[$column['title']] = $column['id'];
    }

    write_to_log("Column Map for Board ID $boardId: " . print_r($columnMap, true));
    return $columnMap;
}

// Fetch parent item details
function get_parent_item_details($itemId) {
    write_to_log("[INFO] Fetching parent item details for item ID: $itemId");
    $query = json_encode([
        'query' => "query {
            items(ids: {$itemId}) {
                column_values {
                    id
                    text
                    value
                }
            }
        }"
    ]);
    $response = monday_curl_request($query);

    if (empty($response['data']['items'][0]['column_values'])) {
        write_to_log("[ERROR] No column values found for item ID: $itemId");
        return null;
    }

    write_to_log("Column Values for Item ID $itemId: " . print_r($response['data']['items'][0]['column_values'], true));
    return $response['data']['items'][0]['column_values'];
}

// Fetch sub-item count for a parent item
function get_sub_item_count($parentItemId) {
    write_to_log("[INFO] Fetching sub-item count for parent item ID: $parentItemId");
    $query = json_encode([
        'query' => "query {
            items(ids: {$parentItemId}) {
                subitems {
                    id
                }
            }
        }"
    ]);
    $response = monday_curl_request($query);
    $subItemCount = count($response['data']['items'][0]['subitems'] ?? []);
    write_to_log("Sub-Item Count for Parent Item $parentItemId: $subItemCount");
    return $subItemCount;
}

// Add sub-items to a parent item
function add_sub_items($parentItemId, $eventDate, $campaignTime, $timelineColumnId) {
    write_to_log("[INFO] Adding sub-items for parent item ID: $parentItemId");
    $subItems = [
        ["name" => "Concept", "daysBackStart" => 21, "daysBackEnd" => 20],
        ["name" => "Spec", "daysBackStart" => 19, "daysBackEnd" => 16],
        ["name" => "Asset Creation", "daysBackStart" => 15, "daysBackEnd" => 10],
        ["name" => "Asset Review", "daysBackStart" => 9, "daysBackEnd" => 5],
        ["name" => "Setup Publishing", "daysBackStart" => 3, "daysBackEnd" => 2],
    ];

    $baseDate = new DateTime($eventDate);

    foreach ($subItems as $subItem) {
        $startDate = clone $baseDate;
        $startDate->modify('-' . ($subItem['daysBackStart'] + $campaignTime) . ' days');
        $endDate = clone $baseDate;
        $endDate->modify('-' . ($subItem['daysBackEnd'] + $campaignTime) . ' days');

        write_to_log("Creating Sub-Item: {$subItem['name']} (Start: {$startDate->format('Y-m-d')}, End: {$endDate->format('Y-m-d')})");

        // Create the sub-item
        $mutation = json_encode([
            'query' => "mutation {
                create_subitem(parent_item_id: {$parentItemId}, item_name: \"{$subItem['name']}\") {
                    id
                }
            }"
        ]);
        $response = monday_curl_request($mutation);

        if (!empty($response['data']['create_subitem']['id'])) {
            $subItemId = $response['data']['create_subitem']['id'];
            write_to_log("Sub-Item Created with ID: $subItemId");

            // Update the sub-item with the Timeline column
            $timelineValue = json_encode([
                "from" => $startDate->format('Y-m-d'),
                "to" => $endDate->format('Y-m-d')
            ]);

            $updateMutation = json_encode([
                'query' => "mutation {
                    change_column_value(item_id: {$subItemId}, column_id: \"{$timelineColumnId}\", value: \"{$timelineValue}\") {
                        id
                    }
                }"
            ]);
            $updateResponse = monday_curl_request($updateMutation);

            if (!empty($updateResponse['data'])) {
                write_to_log("Sub-Item Updated with Timeline: $subItemId");
            } else {
                write_to_log("[ERROR] Failed to update Sub-Item Timeline: {$subItem['name']}");
            }
        } else {
            write_to_log("[ERROR] Failed to create Sub-Item: {$subItem['name']}");
        }
    }
}

// Handle incoming webhook payloads
function handle_webhook($payload) {
    write_to_log("Webhook Received: " . print_r($payload, true));

    // Check if the updated column is the Event Date
    if (isset($payload['event']['type']) && $payload['event']['type'] === 'update_column_value' && $payload['event']['columnId'] === 'date') {
        $item_id = $payload['event']['pulseId']; // Use pulseId instead of itemId
        $board_id = $payload['event']['boardId'];

        write_to_log("[INFO] Processing Event Date update for item ID: $item_id on board ID: $board_id");

        // Fetch column IDs for the board
        $columnMap = fetch_column_ids($board_id);
        if (!$columnMap) {
            write_to_log("[ERROR] Failed to fetch column IDs for board ID: $board_id");
            http_response_code(400);
            echo json_encode(['error' => 'Failed to fetch column IDs.']);
            exit;
        }

        // Fetch parent item details
        $parentDetails = get_parent_item_details($item_id);
        if (!$parentDetails) {
            write_to_log("[ERROR] Failed to fetch parent item details for item ID: $item_id");
            http_response_code(400);
            echo json_encode(['error' => 'Failed to fetch parent item details.']);
            exit;
        }

        // Parse parent details to get Event Date and Campaign Time
        $eventDate = null;
        $campaignTime = 0;
        foreach ($parentDetails as $column) {
            if ($column['id'] === 'date') {
                $eventDate = $column['text'];
            }
            if ($column['id'] === 'numbers_mkm7t605') { // Replace with the actual column ID for Campaign Time
                $campaignTime = (int)$column['text'];
            }
        }

        if (!$eventDate) {
            write_to_log("[ERROR] Event Date not found for Parent Item ID: $item_id");
            http_response_code(400);
            echo json_encode(['error' => 'Event Date not found.']);
            exit;
        }

        // Check if sub-items already exist
        if (get_sub_item_count($item_id) === 0) {
            // Add sub-items
            $timelineColumnId = $columnMap['Timeline'] ?? null; // Use "Timeline" instead of "Task Timeline"
            if (!$timelineColumnId) {
                write_to_log("[ERROR] Timeline column not found.");
                http_response_code(400);
                echo json_encode(['error' => 'Timeline column not found.']);
                exit;
            }

            add_sub_items($item_id, $eventDate, $campaignTime, $timelineColumnId);
            write_to_log('[SUCCESS] Sub-items added successfully.');
            http_response_code(200);
            echo json_encode(['success' => 'Sub-items added successfully.']);
        } else {
            write_to_log('[INFO] Sub-items already exist. No changes made.');
            http_response_code(200);
            echo json_encode(['message' => 'Sub-items already exist. No changes made.']);
        }
    } else {
        write_to_log("[INFO] Webhook ignored. Event type or column ID does not match.");
    }
}