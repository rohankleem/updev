<?php
/* Template Name: Webhook Monday Subitem Populate 2 */

require_once __DIR__ . '/../../../../wp-load.php';

// Monday.com API Token
define('MONDAY_API_TOKEN', $_ENV['MONDAY_API_TOKEN']);
// Fallback constant for the subitem Timeline column ID (set in your environment)
define('MONDAY_SUBITEM_TIMELINE_COLUMN_ID', 'timeline_mkmss9h7');

// Column Titles (update these if column names change in Monday.com)
$taskColumnTitles = [
    'Event',
    'Owner',
    'Status',
    'Event Date',
    'Campaign Run Time'
];

$subtaskColumnTitles = [
    'Subitem',
    'Owner',
    'Status',
    'Timeline'
];

// Main Webhook Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = file_get_contents('php://input');
    $payload = json_decode($payload, true);

    handleWebhook($payload);
}

// Function to make a cURL request
function mondayCurlRequest($query)
{
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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    writeToLog("HTTP Code: $httpCode");
    writeToLog("Response: $response");
    if ($error) {
        writeToLog("cURL Error: $error");
    }

    return json_decode($response, true);
}

// Logging Function
function writeToLog($message)
{
    $logFile = WP_CONTENT_DIR . '/themes/buildio2/custom-logs/monday-webhook.log';

    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }

    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Fetch column IDs for a board (for parent items)
function fetchColumnIds($boardId, $columnTitles)
{
    writeToLog("[INFO] Fetching column IDs for board ID: $boardId");
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
    $response = mondayCurlRequest($query);

    if (empty($response['data']['boards'][0]['columns'])) {
        writeToLog("[ERROR] No columns found for board ID: $boardId");
        return null;
    }

    $columns = $response['data']['boards'][0]['columns'];
    $columnMap = [];

    // Map column titles to IDs
    foreach ($columns as $column) {
        if (in_array($column['title'], $columnTitles)) {
            $columnMap[$column['title']] = $column['id'];
        }
    }

    writeToLog("Column Map for Board ID $boardId: " . print_r($columnMap, true));
    return $columnMap;
}

// Fetch subitem column IDs for a board (for subitems)
// Note: Monday.com's API currently does not support querying subitem columns directly.
// We attempt the query and, on error, fallback to using environment variables.
function fetchSubitemColumnIds($boardId, $columnTitles)
{
    writeToLog("[INFO] Attempting to fetch subitem column IDs for board ID: $boardId");
    $query = json_encode([
        'query' => "query {
            boards(ids: {$boardId}) {
                subitem_columns {
                    id
                    title
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);

    // If errors are returned, use our fallback.
    if (isset($response['errors'])) {
        writeToLog("[WARNING] Could not fetch subitem columns via API. Using fallback method. Errors: " . print_r($response['errors'], true));
        $fallback = [];
        if (in_array('Timeline', $columnTitles)) {
            if (defined('MONDAY_SUBITEM_TIMELINE_COLUMN_ID') && MONDAY_SUBITEM_TIMELINE_COLUMN_ID) {
                $fallback['Timeline'] = MONDAY_SUBITEM_TIMELINE_COLUMN_ID;
            } else {
                writeToLog("[ERROR] MONDAY_SUBITEM_TIMELINE_COLUMN_ID not defined in the environment.");
            }
        }
        return $fallback;
    }

    if (empty($response['data']['boards'][0]['subitem_columns'])) {
        writeToLog("[ERROR] No subitem columns found for board ID: $boardId");
        return null;
    }

    $columns = $response['data']['boards'][0]['subitem_columns'];
    $columnMap = [];

    // Map column titles to IDs
    foreach ($columns as $column) {
        if (in_array($column['title'], $columnTitles)) {
            $columnMap[$column['title']] = $column['id'];
        }
    }

    writeToLog("Subitem Column Map for Board ID $boardId: " . print_r($columnMap, true));
    return $columnMap;
}

// Fetch parent item details
function getTaskDetails($taskId)
{
    writeToLog("[INFO] Fetching task details for task ID: $taskId");
    $query = json_encode([
        'query' => "query {
            items(ids: {$taskId}) {
                column_values {
                    id
                    text
                    value
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);

    if (empty($response['data']['items'][0]['column_values'])) {
        writeToLog("[ERROR] No column values found for task ID: $taskId");
        return null;
    }

    writeToLog("Column Values for Task ID $taskId: " . print_r($response['data']['items'][0]['column_values'], true));
    return $response['data']['items'][0]['column_values'];
}

// Fetch sub-item count for a parent item
function getSubtaskCount($taskId)
{
    writeToLog("[INFO] Fetching subtask count for task ID: $taskId");
    $query = json_encode([
        'query' => "query {
            items(ids: {$taskId}) {
                subitems {
                    id
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);
    $subtaskCount = count($response['data']['items'][0]['subitems'] ?? []);
    writeToLog("Subtask Count for Task ID $taskId: $subtaskCount");
    return $subtaskCount;
}

// Helper function: Get the board ID for a subitem.
function getSubitemBoardId($subitemId)
{
    writeToLog("[INFO] Fetching board ID for subitem ID: $subitemId");
    $query = json_encode([
        'query' => "query {
            items(ids: {$subitemId}) {
                board {
                    id
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);
    if (!empty($response['data']['items'][0]['board']['id'])) {
        $subitemBoardId = $response['data']['items'][0]['board']['id'];
        writeToLog("[INFO] Found board ID for subitem $subitemId: $subitemBoardId");
        return $subitemBoardId;
    } else {
        writeToLog("[ERROR] Unable to fetch board id for subitem: $subitemId");
        return null;
    }
}

// Add sub-items to a parent item
function addSubtasks($taskId, $taskEventDate, $taskCampaignTime, $subtaskColumnMap, $boardId)
{
    writeToLog("[INFO] Adding subtasks for task ID: $taskId");
    $subtasks = [
        ["name" => "Develop Concept", "daysBackStart" => 21, "daysBackEnd" => 20],
        ["name" => "Create Spec", "daysBackStart" => 19, "daysBackEnd" => 16],
        ["name" => "Create Design Assets", "daysBackStart" => 15, "daysBackEnd" => 10],
        ["name" => "Review Design Assets", "daysBackStart" => 9, "daysBackEnd" => 5],
        ["name" => "Seutp and Publish Organic Posts", "daysBackStart" => 3, "daysBackEnd" => 2],
        ["name" => "Setup and Publish Ads", "daysBackStart" => 3, "daysBackEnd" => 2],
        ["name" => "Email Comms", "daysBackStart" => 3, "daysBackEnd" => 2],
        ["name" => "Campaigns Running", "daysBackStart" => 0, "daysBackEnd" => -$taskCampaignTime],
        ["name" => "Post Event Review", "daysBackStart" => -14 -$taskCampaignTime, "daysBackEnd" => -21 - $taskCampaignTime],
    ];

    $baseDate = new DateTime($taskEventDate);

    foreach ($subtasks as $subtask) {
        $startDate = clone $baseDate;
        $startDate->modify('-' . ($subtask['daysBackStart'] + $taskCampaignTime) . ' days');
        $endDate = clone $baseDate;
        $endDate->modify('-' . ($subtask['daysBackEnd'] + $taskCampaignTime) . ' days');

        writeToLog("Creating Subtask: {$subtask['name']} (Start: " . $startDate->format('Y-m-d') . ", End: " . $endDate->format('Y-m-d') . ")");

        // Create the subtask
        $mutation = json_encode([
            'query' => "mutation {
                create_subitem(parent_item_id: {$taskId}, item_name: \"{$subtask['name']}\") {
                    id
                }
            }"
        ]);
        $response = mondayCurlRequest($mutation);

        if (!empty($response['data']['create_subitem']['id'])) {
            $subtaskId = $response['data']['create_subitem']['id'];
            writeToLog("Subtask Created with ID: $subtaskId");

            // Get the board ID for the subitem (subitems are stored on a separate board)
            $subitemBoardId = getSubitemBoardId($subtaskId);
            if (!$subitemBoardId) {
                writeToLog("[ERROR] Could not retrieve board id for subitem: $subtaskId");
                continue;
            }

            // Prepare the timeline value as a JSON string
            $timelineValue = json_encode([
                "from" => $startDate->format('Y-m-d'),
                "to"   => $endDate->format('Y-m-d')
            ]);
            // Escape quotes for safe inclusion in the GraphQL query string
            $timelineValueEscaped = addslashes($timelineValue);

            // Update the subtask with the Timeline column using change_column_value and the subitem's board ID.
            $updateMutation = json_encode([
                'query' => "mutation {
                    change_column_value(item_id: {$subtaskId}, board_id: {$subitemBoardId}, column_id: \"{$subtaskColumnMap['Timeline']}\", value: \"{$timelineValueEscaped}\") {
                        id
                    }
                }"
            ]);
            writeToLog("Update Mutation: " . $updateMutation);  // Log the mutation for debugging
            $updateResponse = mondayCurlRequest($updateMutation);

            if (!empty($updateResponse['data'])) {
                writeToLog("Subtask Updated with Timeline: $subtaskId");
            } else {
                writeToLog("[ERROR] Failed to update Subtask Timeline: {$subtask['name']}");
            }
        } else {
            writeToLog("[ERROR] Failed to create Subtask: {$subtask['name']}");
        }
    }
}

// New Function: Report the columns for each subtask of the given task
function reportSubtaskColumns($taskId)
{
    writeToLog("[INFO] Reporting subtask columns for task ID: $taskId");
    $query = json_encode([
        'query' => "query {
            items(ids: {$taskId}) {
                subitems {
                    id
                    name
                    column_values {
                        id
                        text
                        value
                    }
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);

    // Check if subitems exist
    if (isset($response['data']['items'][0]['subitems']) && !empty($response['data']['items'][0]['subitems'])) {
        foreach ($response['data']['items'][0]['subitems'] as $subitem) {
            writeToLog("[INFO] Subitem ID: " . $subitem['id'] .
                ", Name: " . $subitem['name'] .
                ", Columns: " . print_r($subitem['column_values'], true));
        }
    } else {
        writeToLog("[INFO] No subitems found for task ID: $taskId. Cannot report subitem columns.");
    }
}

// Handle incoming webhook payloads
function handleWebhook($payload)
{
    writeToLog("Webhook Received: " . print_r($payload, true));

    // Check if the updated column is the Event Date
    if (
        isset($payload['event']['type']) &&
        $payload['event']['type'] === 'update_column_value' &&
        $payload['event']['columnId'] === 'date'
    ) {
        $taskId = $payload['event']['pulseId']; // Use pulseId instead of itemId
        $taskBoardId = $payload['event']['boardId'];

        writeToLog("[INFO] Processing Event Date update for task ID: $taskId on board ID: $taskBoardId");

        // Fetch column IDs for the task (parent columns)
        global $taskColumnTitles;
        $taskColumnMap = fetchColumnIds($taskBoardId, $taskColumnTitles);
        if (!$taskColumnMap) {
            writeToLog("[ERROR] Failed to fetch column IDs for task board ID: $taskBoardId");
            http_response_code(400);
            echo json_encode(['error' => 'Failed to fetch column IDs.']);
            exit;
        }

        // Fetch task details
        $taskDetails = getTaskDetails($taskId);
        if (!$taskDetails) {
            writeToLog("[ERROR] Failed to fetch task details for task ID: $taskId");
            http_response_code(400);
            echo json_encode(['error' => 'Failed to fetch task details.']);
            exit;
        }

        // Parse task details to get Event Date and Campaign Time
        $taskEventDate = null;
        $taskCampaignTime = 0;
        foreach ($taskDetails as $column) {
            if ($column['id'] === $taskColumnMap['Event Date']) {
                $taskEventDate = $column['text'];
            }
            if ($column['id'] === $taskColumnMap['Campaign Run Time']) {
                $taskCampaignTime = (int)$column['text'];
            }
        }

        if (!$taskEventDate) {
            writeToLog("[ERROR] Event Date not found for Task ID: $taskId");
            http_response_code(400);
            echo json_encode(['error' => 'Event Date not found.']);
            exit;
        }

        // Check if subtasks already exist
        if (getSubtaskCount($taskId) === 0) {
            // Fetch subitem column IDs for subtasks (using our fallback logic)
            global $subtaskColumnTitles;
            $subtaskColumnMap = fetchSubitemColumnIds($taskBoardId, $subtaskColumnTitles);

            if (empty($subtaskColumnMap['Timeline'])) {
                writeToLog("[ERROR] Timeline column not found for subtasks. Available subitem columns: " . print_r($subtaskColumnMap, true));
                http_response_code(400);
                echo json_encode(['error' => 'Timeline column not found.']);
                exit;
            }

            // Add subtasks
            addSubtasks($taskId, $taskEventDate, $taskCampaignTime, $subtaskColumnMap, $taskBoardId);
            writeToLog('[SUCCESS] Subtasks added successfully.');
            http_response_code(200);
            echo json_encode(['success' => 'Subtasks added successfully.']);
        } else {
            writeToLog('[INFO] Subtasks already exist. No changes made.');
            http_response_code(200);
            echo json_encode(['message' => 'Subtasks already exist. No changes made.']);
        }

        // Report the subtask columns for the task (for debugging/inspection purposes)
        reportSubtaskColumns($taskId);
    } else {
        writeToLog("[INFO] Webhook ignored. Event type or column ID does not match.");
    }
}
?>
