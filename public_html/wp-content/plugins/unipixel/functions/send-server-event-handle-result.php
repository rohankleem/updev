<?php

//File: public_html\wp-content\plugins\unipixel\functions\send-server-event-handle-result.php

function unipixel_handle_send_event_result(
    $platform_id,
    $element_ref,
    $event_trigger,
    $event_name,
    $sendServerResultInfo,
    $method = null,
    $party = null,
    $event_order = null
) {
    $log = new UniPixelLog();

    // Extract consistent data from unified result
    $wpRemotePostResult   = $sendServerResultInfo['response'] ?? [];
    $wpRemotePostSendArr  = $sendServerResultInfo['args'] ?? [];
    $eventInfoPayloadArr  = $sendServerResultInfo['payload'] ?? [];

    // Ensure response is always a serializable array (wp_remote_post can return WP_Error)
    if (is_wp_error($wpRemotePostResult)) {
        $wpRemotePostResult = [
            'error'    => $wpRemotePostResult->get_error_message(),
            'response' => ['code' => -1, 'message' => $wpRemotePostResult->get_error_message()],
        ];
    }

 

    // Determine HTTP code and message from wp_remote_post() structure
    $responseCode    = isset($wpRemotePostResult['response']['code'])    ? $wpRemotePostResult['response']['code']    : -1;
    $responseMessage = isset($wpRemotePostResult['response']['message']) ? $wpRemotePostResult['response']['message'] : 'Unknown';

    // Build log message based on result context
    $responseLogMessage = "Code: $responseCode, $responseMessage";

    if ($method === 'client') {
        $responseLogMessage = "Client-side event, no response";
    } elseif (in_array((string)$responseCode, ['200', '204'])) {
        $responseLogMessage = "Successful: Code: $responseCode, Ok";
    } elseif ((string)$responseCode === '0') {
        $responseLogMessage = "Response logging turned off for this event";
    } elseif ((string)$responseCode === '1') {
        $responseLogMessage = "Event blocked: Consent not provided";
    }


       // Prepare JSON for database log (what was sent)
    $wpRemotePostSendArr_AsJson = wp_json_encode($wpRemotePostSendArr);

    // Insert log entry into database
    $log->insert_log(
        $platform_id,
        $element_ref,
        $event_trigger,
        $event_name,
        $responseLogMessage,
        $wpRemotePostSendArr_AsJson,
        $method,
        $party,
        $event_order
    );

    // Return a normalized result for any caller expecting JSON
    return wp_json_encode([
        'dataSent'         => $wpRemotePostSendArr,
        'platformResponse' => $wpRemotePostResult,
    ]);
}

