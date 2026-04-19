<?php
/* Template Name: Webhook Monday Subitem Populate 2 */

require_once __DIR__ . '/../../../../wp-load.php';

// Monday.com API Token
define('MONDAY_API_TOKEN', $_ENV['MONDAY_API_TOKEN']);
define('MONDAY_SUBITEM_TIMELINE_COLUMN_ID', $_ENV['MONDAY_SUBITEM_TIMELINE_COLUMN_ID']);

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

//-------------------------------------------------
// API & Logging Functions
//-------------------------------------------------

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

function writeToLog($message)
{
    $logFile = WP_CONTENT_DIR . '/themes/buildio2/custom-logs/monday-webhook.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

//-------------------------------------------------
// Column & Task Helper Functions
//-------------------------------------------------

function fetchColumnIds($boardId, $columnTitles)
{
    writeToLog("[INFO] Fetching column IDs for board ID: $boardId");
    // Pass board id as an array (per API requirements)
    $query = json_encode([
        'query' => "query {
            boards(ids: [{$boardId}]) {
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
    foreach ($columns as $column) {
        if (in_array($column['title'], $columnTitles)) {
            $columnMap[$column['title']] = $column['id'];
        }
    }
    writeToLog("Column Map for Board ID $boardId: " . print_r($columnMap, true));
    return $columnMap;
}

function fetchSubitemColumnIds($boardId, $columnTitles)
{
    writeToLog("[INFO] Fetching subitem column IDs for board ID: $boardId");
    $query = json_encode([
        'query' => "query {
            boards(ids: [{$boardId}]) {
                subitem_columns {
                    id
                    title
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);
    if (isset($response['errors'])) {
        writeToLog("[WARNING] Could not fetch subitem columns via API. Using fallback. Errors: " . print_r($response['errors'], true));
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
    foreach ($columns as $column) {
        if (in_array($column['title'], $columnTitles)) {
            $columnMap[$column['title']] = $column['id'];
        }
    }
    writeToLog("Subitem Column Map for Board ID $boardId: " . print_r($columnMap, true));
    return $columnMap;
}

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

//-------------------------------------------------
// Subtask Sync Functions
//-------------------------------------------------

// Create a single subtask and return its ID.
function addSubtask($taskId, $expected)
{
    writeToLog("[INFO] Adding subtask: {$expected['name']}");
    $mutation = json_encode([
        'query' => "mutation {
            create_subitem(parent_item_id: {$taskId}, item_name: \"{$expected['name']}\") {
                id
            }
        }"
    ]);
    $response = mondayCurlRequest($mutation);
    if (!empty($response['data']['create_subitem']['id'])) {
        $subtaskId = $response['data']['create_subitem']['id'];
        writeToLog("[INFO] Subtask '{$expected['name']}' created with ID: $subtaskId");
        return $subtaskId;
    } else {
        writeToLog("[ERROR] Failed to create subtask: {$expected['name']}");
        return null;
    }
}

// Update the timeline for a given subtask.
function updateTimelineForSubtask($subtaskId, $expected, $taskEventDate, $taskCampaignTime)
{
    $baseDate = new DateTime($taskEventDate);
    $startOffset = $expected['daysBackStart'] + $taskCampaignTime;
    $endOffset = $expected['daysBackEnd'] + $taskCampaignTime;
    $startDate = clone $baseDate;
    $startDate->modify('-' . $startOffset . ' days');
    $endDate = clone $baseDate;
    $endDate->modify('-' . $endOffset . ' days');
    $timelineValue = json_encode([
        "from" => $startDate->format('Y-m-d'),
        "to"   => $endDate->format('Y-m-d')
    ]);
    $timelineValueEscaped = addslashes($timelineValue);
    $subitemBoardId = getSubitemBoardId($subtaskId);
    if (!$subitemBoardId) {
        writeToLog("[ERROR] Could not retrieve board id for subtask: $subtaskId");
        return;
    }
    $subitemColumnMap = fetchSubitemColumnIds($subitemBoardId, ['Timeline']);
    if (empty($subitemColumnMap['Timeline'])) {
        writeToLog("[ERROR] Timeline column not found for subitem board id: $subitemBoardId");
        return;
    }
    $updateMutation = json_encode([
        'query' => "mutation {
            change_column_value(item_id: {$subtaskId}, board_id: {$subitemBoardId}, column_id: \"{$subitemColumnMap['Timeline']}\", value: \"{$timelineValueEscaped}\") {
                id
            }
        }"
    ]);
    writeToLog("Update Mutation for subtask '{$expected['name']}' (ID: $subtaskId): " . $updateMutation);
    $updateResponse = mondayCurlRequest($updateMutation);
    if (!empty($updateResponse['data'])) {
        writeToLog("[INFO] Subtask '{$expected['name']}' (ID: $subtaskId) timeline updated successfully.");
    } else {
        writeToLog("[ERROR] Failed to update timeline for subtask '{$expected['name']}' (ID: $subtaskId).");
    }
}

// Sync expected subtasks: update timeline if exists; if not, add then update.
function syncSubtasks($taskId, $taskEventDate, $taskCampaignTime, $taskBoardId)
{
    writeToLog("[INFO] Syncing subtasks for task ID: $taskId");
    // Expected subtask definitions (using your original equations)
    $expectedSubtasks = [
        ["name" => "Develop Concept", "daysBackStart" => 21, "daysBackEnd" => 20],
        ["name" => "Create Spec", "daysBackStart" => 19, "daysBackEnd" => 16],
        ["name" => "Create Design Assets", "daysBackStart" => 15, "daysBackEnd" => 10],
        ["name" => "Review Design Assets", "daysBackStart" => 9, "daysBackEnd" => 5],
        ["name" => "Seutp and Publish Organic Posts", "daysBackStart" => 3, "daysBackEnd" => 2],
        ["name" => "Setup and Publish Ads", "daysBackStart" => 3, "daysBackEnd" => 2],
        ["name" => "Email Comms", "daysBackStart" => 3, "daysBackEnd" => 2],
        ["name" => "Campaigns Running", "daysBackStart" => 0, "daysBackEnd" => -$taskCampaignTime],
        ["name" => "Post Event Review", "daysBackStart" => -14 - $taskCampaignTime, "daysBackEnd" => -21 - $taskCampaignTime],
    ];
    // Query for existing subitems of the parent task.
    $query = json_encode([
        'query' => "query {
            items(ids: {$taskId}) {
                subitems {
                    id
                    name
                }
            }
        }"
    ]);
    $response = mondayCurlRequest($query);
    $existingSubitems = [];
    if (!empty($response['data']['items'][0]['subitems'])) {
        $existingSubitems = $response['data']['items'][0]['subitems'];
    }
    foreach ($expectedSubtasks as $expected) {
        $found = false;
        foreach ($existingSubitems as $subitem) {
            if (trim($subitem['name']) === trim($expected['name'])) {
                $found = true;
                updateTimelineForSubtask($subitem['id'], $expected, $taskEventDate, $taskCampaignTime);
                break;
            }
        }
        if (!$found) {
            $newSubtaskId = addSubtask($taskId, $expected);
            if ($newSubtaskId) {
                updateTimelineForSubtask($newSubtaskId, $expected, $taskEventDate, $taskCampaignTime);
            }
        }
    }
}

//-------------------------------------------------
// Webhook Handler
//-------------------------------------------------

function handleWebhook($payload)
{
    writeToLog("Webhook Received: " . print_r($payload, true));
    if (
        isset($payload['event']['type']) &&
        $payload['event']['type'] === 'update_column_value'
    ) {
        $taskId = $payload['event']['pulseId'];
        $taskBoardId = $payload['event']['boardId'];
        writeToLog("[INFO] Processing update for task ID: $taskId on board ID: $taskBoardId");
        global $taskColumnTitles;
        $taskColumnMap = fetchColumnIds($taskBoardId, $taskColumnTitles);
        if (!$taskColumnMap) {
            writeToLog("[ERROR] Failed to fetch column IDs for task board ID: $taskBoardId");
            http_response_code(400);
            echo json_encode(['error' => 'Failed to fetch column IDs.']);
            exit;
        }
        $taskDetails = getTaskDetails($taskId);
        if (!$taskDetails) {
            writeToLog("[ERROR] Failed to fetch task details for task ID: $taskId");
            http_response_code(400);
            echo json_encode(['error' => 'Failed to fetch task details.']);
            exit;
        }
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
        // If the trigger is from either Event Date or Campaign Run Time:
        if (
            $payload['event']['columnId'] === $taskColumnMap['Event Date'] ||
            $payload['event']['columnId'] === $taskColumnMap['Campaign Run Time']
        ) {
            // Instead of a separate branch for add vs. update, we sync every expected subtask.
            syncSubtasks($taskId, $taskEventDate, $taskCampaignTime, $taskBoardId);
            writeToLog("[SUCCESS] Subtasks synchronized successfully.");
            http_response_code(200);
            echo json_encode(['success' => 'Subtasks synchronized successfully.']);
        } else {
            writeToLog("[INFO] Change not in Event Date or Campaign Run Time column. No updates made.");
            http_response_code(200);
            echo json_encode(['message' => 'No updates made.']);
        }
       // reportSubtaskColumns($taskId);
    } else {
        writeToLog("[INFO] Webhook ignored. Event type does not match.");
    }
}
?>
