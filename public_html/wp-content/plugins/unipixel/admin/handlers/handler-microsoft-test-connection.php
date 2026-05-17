<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for Test Connection on the Microsoft setup page.
 *
 * Validation flow:
 *   1. Format checks (UET Tag ID = 7-12 digits, CAPI Access Token = 30+ alphanumeric)
 *   2. POST a sample event to https://capi.uet.microsoft.com/v1/{tag_id}/events
 *      with Authorization: Bearer <token>
 *   3. Interpret the response (Microsoft returns JSON with error.code + error.message):
 *        - WP_Error    -> network error
 *        - HTTP 401    -> token invalid/revoked
 *        - HTTP 403    -> token lacks permission
 *        - HTTP 404    -> tag_id wrong or no access
 *        - HTTP 200    -> success
 *        - HTTP 4xx    -> parse error.code/message and surface
 *        - HTTP 5xx    -> Microsoft server error
 *
 * Endpoint verified via direct curl 2026-05-13.
 *
 * Note: Microsoft CAPI is token-gated for many users (you may need to contact
 * your Microsoft Ads account manager to get one). If the user doesn't have a
 * token, this Test Connection won't help them; they need to acquire one first.
 */

add_action('wp_ajax_unipixel_microsoft_test_connection', 'unipixel_handle_microsoft_test_connection');

function unipixel_handle_microsoft_test_connection()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'state'   => 'forbidden',
            'message' => __('You do not have permission to run this action.', 'unipixel'),
        ), 403);
        return;
    }

    $tag_id       = isset($_POST['pixel_id']) ? sanitize_text_field(wp_unslash($_POST['pixel_id'])) : '';
    $access_token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';

    if ($tag_id === '') {
        wp_send_json_error(array(
            'state'   => 'no_tag_id',
            'message' => __('No UET Tag ID entered. Paste your Microsoft UET Tag ID above first.', 'unipixel'),
        ));
        return;
    }

    if ($access_token === '') {
        wp_send_json_error(array(
            'state'   => 'no_token',
            'message' => __('No CAPI Access Token entered. Microsoft\'s Conversions API tokens are gated; if you don\'t have one yet, contact your Microsoft Advertising account manager to request access.', 'unipixel'),
        ));
        return;
    }

    // Format check: Microsoft UET Tag IDs are numeric, typically 7-9 digits.
    if (!preg_match('/^\d{6,12}$/', $tag_id)) {
        wp_send_json_error(array(
            'state'   => 'invalid_tag_id_format',
            'message' => __('UET Tag ID looks wrong. Microsoft UET Tag IDs are 7-9 digit numbers (e.g. 12345678). Find yours in Microsoft Advertising → Tools → UET tag.', 'unipixel'),
        ));
        return;
    }

    // Format check: CAPI access tokens are long alphanumeric strings (often with
    // underscores or hyphens). Allow 30+ chars to be tolerant.
    if (!preg_match('/^[A-Za-z0-9_\-\.]{30,500}$/', $access_token)) {
        wp_send_json_error(array(
            'state'   => 'invalid_token_format',
            'message' => __('CAPI Access Token looks wrong. Real tokens are long strings of letters, digits and underscores. Did you paste the full token?', 'unipixel'),
        ));
        return;
    }

    // Build test event payload.
    $event_payload = array(
        'data' => array(array(
            'eventType'      => 'custom',
            'eventName'      => 'unipixel_test_connection',
            'eventId'        => 'unipixel_test_' . time() . '_' . wp_generate_password(8, false, false),
            'eventTime'      => gmdate('Y-m-d\TH:i:s\Z'),
            'pageLocation'   => home_url('/unipixel-test-connection'),
        )),
    );

    $endpoint = 'https://capi.uet.microsoft.com/v1/' . rawurlencode($tag_id) . '/events';

    $response = wp_remote_post($endpoint, array(
        'timeout' => 10,
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'body'    => wp_json_encode($event_payload),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Microsoft. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $response->get_error_message(),
        ));
        return;
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body  = wp_remote_retrieve_body($response);
    $body      = json_decode($raw_body, true);

    $ms_error_code    = '';
    $ms_error_message = '';
    if (is_array($body) && isset($body['error']) && is_array($body['error'])) {
        $ms_error_code    = isset($body['error']['code']) ? (string) $body['error']['code'] : '';
        $ms_error_message = isset($body['error']['message']) ? (string) $body['error']['message'] : '';
    }

    if ($http_code === 401) {
        wp_send_json_error(array(
            'state'         => 'invalid_token',
            'message'       => __('Microsoft rejected the CAPI access token (HTTP 401 Unauthorized). The token is invalid, revoked, or expired. Regenerate it via the Microsoft Advertising UI under "Use Conversions API", or contact your Microsoft Ads account manager.', 'unipixel'),
            'microsoft_msg' => $ms_error_message,
        ));
        return;
    }

    if ($http_code === 403) {
        wp_send_json_error(array(
            'state'         => 'token_missing_permission',
            'message'       => __('Microsoft says the token does not have permission for this Tag (HTTP 403). The token may belong to a different account, or it lacks the required role. Regenerate it for an account that owns this UET Tag.', 'unipixel'),
            'microsoft_msg' => $ms_error_message,
        ));
        return;
    }

    if ($http_code === 404) {
        wp_send_json_error(array(
            'state'         => 'no_tag_access',
            'message'       => sprintf(
                /* translators: %s is the Microsoft UET Tag ID */
                __('Microsoft could not find UET Tag %s, or this token does not have access to it. Double-check the Tag ID in Microsoft Advertising → Tools → UET tag.', 'unipixel'),
                $tag_id
            ),
            'microsoft_msg' => $ms_error_message,
        ));
        return;
    }

    if ($http_code === 429) {
        wp_send_json_error(array(
            'state'   => 'rate_limited',
            'message' => __('Microsoft is rate-limiting requests right now (HTTP 429). Wait a few minutes and try again.', 'unipixel'),
        ));
        return;
    }

    if ($http_code >= 500) {
        wp_send_json_error(array(
            'state'   => 'platform_server_error',
            'message' => sprintf(
                /* translators: %d is the HTTP status code Microsoft returned */
                __('Microsoft server error (HTTP %d). This is on Microsoft\'s side, try again in a few minutes.', 'unipixel'),
                $http_code
            ),
        ));
        return;
    }

    if ($http_code >= 400) {
        wp_send_json_error(array(
            'state'         => 'invalid_request',
            'message'       => $ms_error_message !== ''
                ? sprintf(
                    /* translators: %1$d is the HTTP status code, %2$s is the Microsoft error code, %3$s is Microsoft's error message */
                    __('Microsoft rejected the request (HTTP %1$d, code: %2$s): %3$s', 'unipixel'),
                    $http_code,
                    $ms_error_code,
                    $ms_error_message
                )
                : sprintf(
                    /* translators: %d is the HTTP status code Microsoft returned */
                    __('Microsoft rejected the request with HTTP %d but gave no message.', 'unipixel'),
                    $http_code
                ),
            'microsoft_code' => $ms_error_code,
            'microsoft_msg'  => $ms_error_message,
        ));
        return;
    }

    if ($http_code !== 200) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => sprintf(
                /* translators: %d is the HTTP status code Microsoft returned */
                __('Microsoft returned an unexpected status (HTTP %d). Try again.', 'unipixel'),
                $http_code
            ),
        ));
        return;
    }

    // All checks passed. Record the timestamp.
    $timestamps = get_option('unipixel_test_connection_timestamps', array());
    if (!is_array($timestamps)) {
        $timestamps = array();
    }
    $timestamps[5] = time(); // platform_id 5 = Microsoft
    update_option('unipixel_test_connection_timestamps', $timestamps, false);

    wp_send_json_success(array(
        'state'   => 'connected',
        'message' => sprintf(
            /* translators: %s is the Microsoft UET Tag ID */
            __('Connected. Test event accepted by Microsoft CAPI for UET Tag %s. To confirm events are showing up in Microsoft Advertising reporting, open Microsoft Advertising → Tools → UET tag → your tag → and look at the Activity / events feed.', 'unipixel'),
            $tag_id
        ),
        'tag_id'  => $tag_id,
    ));
}
