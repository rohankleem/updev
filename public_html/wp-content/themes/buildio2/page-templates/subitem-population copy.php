<?php
/* Template Name: Webhook Monday Subitem Populate 2 */

require_once __DIR__ . '/../../../../wp-load.php';






// Monday.com API Token
define('MONDAY_API_TOKEN', $_ENV['MONDAY_API_TOKEN']);

// Your custom token for validating the request
define('VALIDATION_TOKEN', 'fk3043kf52');

// Start Logging
write_to_log('[START] Webhook triggered.');


test_monday_auth();
exit();

monday_direct_query();




write_to_log('MONDAY_API_TOKEN: ' . MONDAY_API_TOKEN);

// Check if the request contains a "challenge" field for verification
$requestBody = file_get_contents('php://input');
$requestData = json_decode($requestBody, true);
write_to_log('Request Body: ' . print_r($requestData, true));

if (isset($requestData['challenge'])) {
    write_to_log('Responding to challenge verification.');
    header('Content-Type: application/json');
    echo json_encode(['challenge' => $requestData['challenge']]);
    exit;
}

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    write_to_log('[ERROR] Invalid request method.');
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method. Only POST is allowed.']);
    exit;
}

// Validate the payload
$parentItemId = $requestData['event']['pulseId'] ?? null;
$columnId = $requestData['event']['columnId'] ?? null;

if (!$parentItemId || !$columnId || trim(strtolower($columnId)) !== 'date') {
    write_to_log('[DEBUG] Validation failed.');
    write_to_log('pulseId: ' . $parentItemId);
    write_to_log('columnId: ' . $columnId);
    write_to_log('Expected columnId: date');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload or column not Event Date.']);
    exit;
}

// Extract Parent Item ID
$parentItemId = $requestData['event']['itemId'];
write_to_log("Parent Item ID: $parentItemId");

// Function to query Monday API
function monday_api_request($query, $variables = []) {

    $tokenX = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjQ1ODYyNzU2MSwiYWFpIjoxMSwidWlkIjo3MDg5NTY5OSwiaWFkIjoiMjAyNS0wMS0xNlQwMDowMDo1Ni4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6Mjc0NzIyNjYsInJnbiI6ImFwc2UyIn0._uaIlc2OuBFgIpHZAVCAOg2IEENbULKVpgEvcsGmPo4";

    $url = 'https://api.monday.com/v2';
    $headers = [
        'Authorization: Bearer ' . $tokenX,
        'Content-Type: application/json',
    ];

    //$headers = ['Content-Type: application/json', 'Authorization: ' . $tokenX];

    write_to_log('[DEBUG] Headers: ' . print_r($headers, true));

    $body = json_encode(['query' => $query, 'variables' => $variables]);

    write_to_log("API Request - Query: $query");
    write_to_log("API Request - Variables: " . print_r($variables, true));

    $response = wp_remote_post($url, [
        'headers' => $headers,
        'body'    => $body,
    ]);

    if (is_wp_error($response)) {
        write_to_log('[ERROR] API Request Failed: ' . $response->get_error_message());
        return ['error' => $response->get_error_message()];
    }

    $responseBody = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($responseBody['errors'])) {
        write_to_log('[ERROR] API Response Errors: ' . print_r($responseBody['errors'], true));
    }

    write_to_log('API Response: ' . print_r($responseBody, true));
    return $responseBody;
}

// Fetch Parent Item Details
function get_parent_item_details($itemId) {
    $query = "
        query {
            items(ids: {$itemId}) {
                column_values {
                    id
                    text
                    value
                }
            }
        }
    ";
    $response = monday_api_request($query);

    if (empty($response['data']['items'][0]['column_values'])) {
        write_to_log("[ERROR] No column values found for item ID: $itemId");
        echo json_encode(['error' => 'No column values found for the specified item ID.', 'response' => $response]);
        exit;
    }

    write_to_log("Column Values for Item ID $itemId: " . print_r($response['data']['items'][0]['column_values'], true));
    return $response['data']['items'][0]['column_values'];
}

// Fetch Sub-Items
function get_sub_item_count($parentItemId) {
    $query = "
        query {
            items(ids: {$parentItemId}) {
                subitems {
                    id
                }
            }
        }
    ";
    $response = monday_api_request($query);
    $subItemCount = count($response['data']['items'][0]['subitems'] ?? []);
    write_to_log("Sub-Item Count for Parent Item $parentItemId: $subItemCount");
    return $subItemCount;
}

// Add Sub-Items
function add_sub_items($parentItemId, $eventDate, $campaignTime) {
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

        $mutation = "
            mutation {
                create_subitem(parent_item_id: {$parentItemId}, item_name: \"{$subItem['name']}\") {
                    id
                }
            }
        ";
        $response = monday_api_request($mutation);

        if (!empty($response['data']['create_subitem']['id'])) {
            $subItemId = $response['data']['create_subitem']['id'];
            write_to_log("Sub-Item Created with ID: $subItemId");
        } else {
            write_to_log("[ERROR] Failed to create Sub-Item: {$subItem['name']}");
        }
    }
}

// Main Logic
$parentDetails = get_parent_item_details($parentItemId);
$eventDate = null;
$campaignTime = 0;

// Parse Parent Details
foreach ($parentDetails as $column) {
    if ($column['id'] === 'date') {
        $eventDate = $column['text'];
    }
    if ($column['id'] === 'numbers_mkm7t605') {
        $campaignTime = (int)$column['text'];
    }
}

if (!$eventDate) {
    write_to_log("[ERROR] Event Date not found for Parent Item ID: $parentItemId");
    http_response_code(400);
    echo json_encode(['error' => 'Event Date not found.']);
    exit;
}

// Check if Sub-Items Already Exist
if (get_sub_item_count($parentItemId) === 0) {
    add_sub_items($parentItemId, $eventDate, $campaignTime);
    write_to_log('[SUCCESS] Sub-items added successfully.');
    http_response_code(200);
    echo json_encode(['success' => 'Sub-items added successfully.']);
} else {
    write_to_log('[INFO] Sub-items already exist. No changes made.');
    http_response_code(200);
    echo json_encode(['message' => 'Sub-items already exist. No changes made.']);
}




function monday_direct_query() {

    write_to_log("[INFO] IN monday_direct_query");

    $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjQ1ODYyNzU2MSwiYWFpIjoxMSwidWlkIjo3MDg5NTY5OSwiaWFkIjoiMjAyNS0wMS0xNlQwMDowMDo1Ni4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6Mjc0NzIyNjYsInJnbiI6ImFwc2UyIn0._uaIlc2OuBFgIpHZAVCAOg2IEENbULKVpgEvcsGmPo4';
    $apiUrl = 'https://api.monday.com/v2';
    $headers = ['Content-Type: application/json', 'Authorization: ' . $token];
    
    $query = '{ boards { id name } }';
    $data = @file_get_contents($apiUrl, false, stream_context_create([
     'http' => [
      'method' => 'POST',
      'header' => $headers,
      'content' => json_encode(['query' => $query]),
     ]
    ]));
    $responseContent = json_decode($data, true);

    write_to_log("data: $data");
    
    echo json_encode($responseContent);
}






// Logging Function
function write_to_log($message) {
    $log_file = WP_CONTENT_DIR . '/themes/buildio2/custom-logs/monday-webhook.log';

    // Create the directory if it doesn't exist
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }

    // Append the message to the log file
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}




function test_monday_auth() {


    write_to_log("[INFO] IN test_monday_auth");

    $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjQ1ODYyNzU2MSwiYWFpIjoxMSwidWlkIjo3MDg5NTY5OSwiaWFkIjoiMjAyNS0wMS0xNlQwMDowMDo1Ni4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6Mjc0NzIyNjYsInJnbiI6ImFwc2UyIn0._uaIlc2OuBFgIpHZAVCAOg2IEENbULKVpgEvcsGmPo4';

    $query = '{
        "query": "query { me { id name } }"
    }';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.monday.com/v2");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Logging for debugging
    write_to_log("HTTP Code: " . $http_code);
    write_to_log("Response: " . $response);
    write_to_log("cURL Error: " . $error);
}
