<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for Test Connection on the TikTok setup page.
 *
 * Validation flow:
 *   1. Format checks on Pixel ID and Access Token
 *   2. POST a sample event to TikTok's Events API with an auto-generated
 *      test_event_code so the event lands in TikTok Events Manager →
 *      Test Events tab (not in production reports).
 *   3. Interpret the response:
 *        - WP_Error    -> network error
 *        - HTTP 401    -> token rejected (verified behaviour: TikTok returns
 *                          401 with empty body for invalid Access-Token header)
 *        - HTTP 200 + code = 0  -> success
 *        - HTTP 200 + code != 0 -> soft error, pass TikTok's message through
 *        - Other       -> unexpected
 *
 * TikTok Events API endpoint: https://business-api.tiktok.com/open_api/v1.3/event/track/
 * Auth: Access-Token header (not bearer)
 * Success response shape: {"code": 0, "message": "OK", "request_id": "...", "data": {}}
 */

add_action('wp_ajax_unipixel_tiktok_test_connection', 'unipixel_handle_tiktok_test_connection');

function unipixel_handle_tiktok_test_connection()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    $access_token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';
    $pixel_id     = isset($_POST['pixel_id']) ? sanitize_text_field(wp_unslash($_POST['pixel_id'])) : '';

    if ($pixel_id === '') {
        wp_send_json_error(array(
            'state'   => 'no_pixel_id',
            'message' => __('No Pixel ID entered. Paste your TikTok Pixel ID above first.', 'unipixel'),
        ));
        return;
    }

    if ($access_token === '') {
        wp_send_json_error(array(
            'state'   => 'no_token',
            'message' => __('No Access Token entered. Paste your TikTok Events API access token above first.', 'unipixel'),
        ));
        return;
    }

    // Format check: TikTok pixel IDs are uppercase alphanumeric, typically 20 chars
    // (e.g. C8C3JPS5R0L0CKHEJ8K0). Allow 18-30 to be tolerant of any variations.
    if (!preg_match('/^[A-Z0-9]{18,30}$/', $pixel_id)) {
        wp_send_json_error(array(
            'state'   => 'invalid_pixel_id_format',
            'message' => __('Pixel ID looks wrong. TikTok Pixel IDs are around 20 characters of uppercase letters and digits (e.g. C8C3JPS5R0L0CKHEJ8K0). Make sure you are not mixing up another platform\'s ID (Meta is numeric, Google starts with G-).', 'unipixel'),
        ));
        return;
    }

    // Format check: TikTok access tokens are 30+ alphanumeric characters (no
    // separators like Meta's "EAA" prefix or Google's URL-safe base64 shape).
    if (!preg_match('/^[A-Za-z0-9]{30,80}$/', $access_token)) {
        wp_send_json_error(array(
            'state'   => 'invalid_token_format',
            'message' => __('Access Token looks wrong. TikTok Events API access tokens are 30+ characters of letters and digits. Did you paste the full token?', 'unipixel'),
        ));
        return;
    }

    // Build the test event payload. test_event_code routes the event to the
    // Test Events tab in TikTok Events Manager so it doesn't pollute production.
    $test_event_code = 'UP_TEST_' . wp_generate_password(8, false, false);
    $event_id        = 'unipixel_test_' . time() . '_' . wp_generate_password(8, false, false);

    $payload = array(
        'event_source'    => 'web',
        'event_source_id' => $pixel_id,
        'test_event_code' => $test_event_code,
        'data'            => array(array(
            'event'      => 'unipixel_test_connection',
            'event_id'   => $event_id,
            'event_time' => time(),
            'context'    => array(
                'user' => (object) array(),
                'page' => array(
                    'url' => home_url('/unipixel-test-connection'),
                ),
            ),
        )),
    );

    $response = wp_remote_post('https://business-api.tiktok.com/open_api/v1.3/event/track/', array(
        'timeout' => 10,
        'headers' => array(
            'Content-Type' => 'application/json',
            'Access-Token' => $access_token,
        ),
        'body'    => wp_json_encode($payload),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach TikTok. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $response->get_error_message(),
        ));
        return;
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body  = wp_remote_retrieve_body($response);

    // TikTok returns 401 with empty body for invalid Access-Token header.
    if ($http_code === 401) {
        wp_send_json_error(array(
            'state'   => 'invalid_token',
            'message' => __('TikTok rejected the access token (HTTP 401). The token is invalid, revoked, or expired. Regenerate it in TikTok Events Manager → Pixels → your Pixel → Settings → Access Token.', 'unipixel'),
        ));
        return;
    }

    if ($http_code === 403) {
        wp_send_json_error(array(
            'state'   => 'token_missing_permission',
            'message' => __('TikTok rejected the access token (HTTP 403). The token does not have permission to access this Pixel. Regenerate it under a user/role that has access to this Pixel and Advertiser.', 'unipixel'),
        ));
        return;
    }

    if ($http_code === 429) {
        wp_send_json_error(array(
            'state'   => 'rate_limited',
            'message' => __('TikTok is rate-limiting requests right now (HTTP 429). Wait a few minutes and try again.', 'unipixel'),
        ));
        return;
    }

    if ($http_code >= 500) {
        wp_send_json_error(array(
            'state'   => 'platform_server_error',
            'message' => sprintf(
                /* translators: %d is the HTTP status code TikTok returned */
                __('TikTok server error (HTTP %d). This is on TikTok\'s side, try again in a few minutes.', 'unipixel'),
                $http_code
            ),
        ));
        return;
    }

    $body = json_decode($raw_body, true);
    if (!is_array($body) || !isset($body['code'])) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => sprintf(
                /* translators: %d is the HTTP status code TikTok returned */
                __('TikTok returned an unexpected response (HTTP %d). Try again.', 'unipixel'),
                $http_code
            ),
        ));
        return;
    }

    $tt_code    = (int) $body['code'];
    $tt_message = isset($body['message']) ? (string) $body['message'] : '';

    // TikTok convention: code 0 = OK.
    if ($tt_code !== 0) {
        $diagnosis = unipixel_diagnose_tiktok_error($tt_code, $tt_message, $pixel_id);
        wp_send_json_error(array(
            'state'         => $diagnosis['state'],
            'message'       => $diagnosis['message'],
            'tiktok_code'   => $tt_code,
            'tiktok_msg'    => $tt_message,
        ));
        return;
    }

    // All checks passed. Record the timestamp for the persistent status indicator.
    $timestamps = get_option('unipixel_test_connection_timestamps', array());
    if (!is_array($timestamps)) {
        $timestamps = array();
    }
    $timestamps[3] = time(); // platform_id 3 = TikTok
    update_option('unipixel_test_connection_timestamps', $timestamps, false);

    wp_send_json_success(array(
        'state'           => 'connected',
        'message'         => sprintf(
            /* translators: %s is the auto-generated test_event_code value */
            __('Connected. Test event accepted by TikTok with code 0 (OK). The event was tagged with test_event_code "%s" so it landed in TikTok Events Manager → Test Events tab (not in production reports). Open Events Manager → your Pixel → Test Events to see "unipixel_test_connection" there.', 'unipixel'),
            $test_event_code
        ),
        'test_event_code' => $test_event_code,
    ));
}

/**
 * Translate a TikTok Events API soft-error code into a Test Connection state +
 * actionable message. Loose mapping; TikTok publishes specific codes per
 * endpoint and the set is large. We pattern-match on common signals and pass
 * TikTok's raw message through as the fallback so the user has a string to
 * search.
 *
 * @param int    $code    TikTok's numeric code from the JSON response body
 * @param string $message TikTok's text message
 * @param string $pixel_id The Pixel ID being tested (for actionable messages)
 * @return array { state: string, message: string }
 */
function unipixel_diagnose_tiktok_error($code, $message, $pixel_id)
{
    $lower = strtolower($message);

    // Pattern match on common TikTok error message signals.
    if (strpos($lower, 'access token') !== false || strpos($lower, 'access-token') !== false || strpos($lower, 'access_token') !== false) {
        return array(
            'state'   => 'invalid_token',
            'message' => __('TikTok rejected the access token. The token is invalid, revoked, or expired. Regenerate it in TikTok Events Manager → Pixels → your Pixel → Settings.', 'unipixel'),
        );
    }

    if (strpos($lower, 'permission') !== false || strpos($lower, 'unauthor') !== false) {
        return array(
            'state'   => 'token_missing_permission',
            'message' => __('TikTok says the token does not have permission for this operation. Regenerate it from a user with access to this Pixel and Advertiser.', 'unipixel'),
        );
    }

    if (strpos($lower, 'pixel') !== false && (strpos($lower, 'not exist') !== false || strpos($lower, 'invalid') !== false || strpos($lower, 'not found') !== false)) {
        return array(
            'state'   => 'no_pixel_access',
            'message' => sprintf(
                /* translators: %s is the TikTok Pixel ID */
                __('TikTok does not recognise Pixel ID %s, or this access token does not have access to it. Double-check the Pixel ID in TikTok Events Manager and that the token belongs to a user who can access this Pixel.', 'unipixel'),
                $pixel_id
            ),
        );
    }

    if (strpos($lower, 'rate') !== false || $code === 40100 || $code === 40101) {
        return array(
            'state'   => 'rate_limited',
            'message' => __('TikTok is rate-limiting requests right now. Wait a few minutes and try again.', 'unipixel'),
        );
    }

    if (strpos($lower, 'parameter') !== false || strpos($lower, 'invalid') !== false) {
        return array(
            'state'   => 'invalid_parameter',
            'message' => $message !== ''
                ? sprintf(/* translators: %s is the raw TikTok error message */ __('TikTok rejected the request as invalid: %s', 'unipixel'), $message)
                : __('TikTok rejected the request as invalid. Most commonly this means the Pixel ID is wrong.', 'unipixel'),
        );
    }

    // Generic fallback: pass TikTok's message through verbatim.
    return array(
        'state'   => 'invalid_response',
        'message' => $message !== ''
            ? sprintf(/* translators: %1$d is TikTok's numeric code, %2$s is TikTok's message */ __('TikTok returned an error (code %1$d): %2$s', 'unipixel'), $code, $message)
            : sprintf(/* translators: %d is TikTok's numeric code */ __('TikTok returned an error (code %d) with no message.', 'unipixel'), $code),
    );
}
