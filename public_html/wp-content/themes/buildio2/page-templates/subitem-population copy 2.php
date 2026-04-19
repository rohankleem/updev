<?php
/* Template Name: Webhook Monday Subitem Populate 2 */

require_once __DIR__ . '/../../../../wp-load.php';

// Monday.com API Token
define('MONDAY_API_TOKEN', $_ENV['MONDAY_API_TOKEN']);

test_monday_auth(); // Toggle this function for debugging
exit();

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

// Function to test authentication
function test_monday_auth() {
    write_to_log("[INFO] Testing Monday API Authentication");
    $query = '{
        "query": "query { me { id name } }"
    }';
    $response = monday_curl_request($query);
    write_to_log("Auth Test Response: " . print_r($response, true));
}

// Logging Function
function write_to_log($message) {
    $log_file = WP_CONTENT_DIR . '/themes/buildio2/custom-logs/monday-webhook.log';
    
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}
