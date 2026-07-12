<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for Test Connection on the Meta setup page.
 *
 * Three-layer validation with diagnostic feedback:
 *   1. Format checks (Pixel ID = 14-17 digits, Access Token = starts with EAA, length plausible)
 *   2. debug_token endpoint - parse is_valid + expires_at + error block
 *   3. Test event send - POST a test_event_code-tagged event to the Pixel's
 *      /events edge and confirm events_received
 *
 * The test event send is the authoritative check. It must NOT be replaced with
 * a scope check or a Pixel node read: the token Events Manager generates for
 * Conversions API today is dataset-scoped (scopes = read_ads_dataset_quality
 * only, granular grant limited to its dataset). Such a token cannot read the
 * Pixel node and carries no ads_management scope, yet sends events perfectly.
 * Gating on either signal rejects exactly the token Meta hands most users.
 *
 * The test event lands only in Events Manager's Test events tab (that is what
 * test_event_code does) and never appears in real data or ad delivery.
 */

add_action('wp_ajax_unipixel_meta_test_connection', 'unipixel_handle_meta_test_connection');

function unipixel_handle_meta_test_connection()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'state'   => 'forbidden',
            'message' => __('You do not have permission to run this action.', 'unipixel'),
        ), 403);
        return;
    }

    $access_token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';
    $pixel_id     = isset($_POST['pixel_id']) ? sanitize_text_field(wp_unslash($_POST['pixel_id'])) : '';

    if ($access_token === '') {
        wp_send_json_error(array(
            'state'   => 'no_token',
            'message' => __('No access token entered. Paste your Meta access token above first.', 'unipixel'),
        ));
        return;
    }

    if ($pixel_id === '') {
        wp_send_json_error(array(
            'state'   => 'no_pixel_id',
            'message' => __('No Pixel ID entered. Paste your Meta Pixel ID above first.', 'unipixel'),
        ));
        return;
    }

    // Format check: Meta Pixel IDs are 14-17 digit numbers.
    if (!preg_match('/^\d{14,17}$/', $pixel_id)) {
        wp_send_json_error(array(
            'state'   => 'invalid_pixel_id_format',
            'message' => __('Pixel ID looks wrong. Meta Pixel IDs are 14-17 digit numbers with no letters or prefix. Make sure you are not mixing up another platform\'s ID (Google Measurement IDs start with "G-"; TikTok and Pinterest IDs look different).', 'unipixel'),
        ));
        return;
    }

    // Format check: Meta access tokens (System User, Page, User) start with "EAA"
    // and are typically 200+ characters. We allow 100+ chars to be tolerant.
    if (substr($access_token, 0, 3) !== 'EAA' || strlen($access_token) < 100) {
        wp_send_json_error(array(
            'state'   => 'invalid_token_format',
            'message' => __('Access token looks wrong. Real Meta access tokens are 200+ characters and start with "EAA". Did you paste the full token? If you pasted an App access token (looks like "app_id|app_secret"), that is the wrong type for Conversions API.', 'unipixel'),
        ));
        return;
    }

    $graph_version = 'v18.0';

    // Step 1: validate the token via debug_token and parse its details.
    $debug_url = 'https://graph.facebook.com/' . $graph_version . '/debug_token?' . http_build_query(array(
        'input_token'  => $access_token,
        'access_token' => $access_token,
    ));

    $debug_response = wp_remote_get($debug_url, array('timeout' => 10));

    if (is_wp_error($debug_response)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Meta. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $debug_response->get_error_message(),
        ));
        return;
    }

    $debug_body = json_decode(wp_remote_retrieve_body($debug_response), true);

    if (!is_array($debug_body)) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => __('Meta returned an unexpected response from debug_token. Try again.', 'unipixel'),
        ));
        return;
    }

    // Top-level error in the debug_token response (malformed token, network problem, etc.)
    if (isset($debug_body['error'])) {
        $diagnosis = unipixel_diagnose_meta_error($debug_body['error']);
        wp_send_json_error(array(
            'state'      => $diagnosis['state'],
            'message'    => $diagnosis['message'],
            'meta_error' => $debug_body['error'],
        ));
        return;
    }

    $data = isset($debug_body['data']) && is_array($debug_body['data']) ? $debug_body['data'] : array();

    // is_valid false - token rejected. data.error usually explains.
    if (empty($data['is_valid'])) {
        if (isset($data['error']) && is_array($data['error'])) {
            $diagnosis = unipixel_diagnose_meta_error($data['error']);
            wp_send_json_error(array(
                'state'      => $diagnosis['state'],
                'message'    => $diagnosis['message'],
                'meta_error' => $data['error'],
            ));
            return;
        }
        wp_send_json_error(array(
            'state'   => 'invalid_token',
            'message' => __('Token is not valid. Check that you copied the full token without extra spaces, or that it has not expired.', 'unipixel'),
        ));
        return;
    }

    // Scopes are reported back to the caller but never gated on (see file docblock).
    $scopes = isset($data['scopes']) && is_array($data['scopes']) ? $data['scopes'] : array();

    // Dataset IDs the token's granular grant covers. Dataset-scoped tokens list
    // the Pixel they were generated for here; used for the wrong-Pixel diagnostic.
    $granular_target_ids = array();
    if (isset($data['granular_scopes']) && is_array($data['granular_scopes'])) {
        foreach ($data['granular_scopes'] as $granular_scope) {
            if (isset($granular_scope['target_ids']) && is_array($granular_scope['target_ids'])) {
                foreach ($granular_scope['target_ids'] as $target_id) {
                    $granular_target_ids[] = (string) $target_id;
                }
            }
        }
    }
    $granular_target_ids = array_values(array_unique($granular_target_ids));

    // Expiry check: if finite expiry (not 0/never) and expiring soon, warn (but don't fail).
    $expires_at      = isset($data['expires_at']) ? (int) $data['expires_at'] : 0;
    $expiry_warning  = '';
    if ($expires_at > 0) {
        $seconds_remaining = $expires_at - time();
        if ($seconds_remaining <= 0) {
            // Defensive - should have been caught by is_valid above.
            wp_send_json_error(array(
                'state'   => 'token_expired',
                'message' => __('Token has expired. Regenerate it in Events Manager (use a long-lived System User token to avoid future expiry).', 'unipixel'),
            ));
            return;
        }
        $days_remaining = (int) floor($seconds_remaining / DAY_IN_SECONDS);
        if ($days_remaining <= 7) {
            $expiry_warning = sprintf(
                /* translators: %d is the number of days until token expiry */
                __(' Warning: this token expires in %d days. Generate a long-lived System User token to avoid tracking interruption.', 'unipixel'),
                $days_remaining
            );
        }
    }

    // Step 2: the authoritative check. Deliver a test event to the Pixel through
    // the Conversions API. test_event_code keeps it out of real data; it is only
    // visible in Events Manager under Test events.
    $test_event_code = 'TEST_UNIPIXEL';
    $events_url      = 'https://graph.facebook.com/' . $graph_version . '/' . rawurlencode($pixel_id) . '/events';

    $event_payload = array(
        array(
            'event_name'       => 'UniPixelConnectionTest',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => home_url('/'),
            'user_data'        => array(
                'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : 'UniPixel Test Connection',
                'client_ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '127.0.0.1',
            ),
        ),
    );

    $events_response = wp_remote_post($events_url, array(
        'timeout' => 15,
        'body'    => array(
            'access_token'    => $access_token,
            'test_event_code' => $test_event_code,
            'data'            => wp_json_encode($event_payload),
        ),
    ));

    if (is_wp_error($events_response)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Meta on the test event send. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $events_response->get_error_message(),
        ));
        return;
    }

    $events_body = json_decode(wp_remote_retrieve_body($events_response), true);

    if (!is_array($events_body)) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => __('Meta returned an unexpected response to the test event. Try again.', 'unipixel'),
        ));
        return;
    }

    if (isset($events_body['error'])) {
        // Most precise diagnosis first: the token's granular grant names the
        // dataset it was generated for. If the entered Pixel ID is not in that
        // list, the user pasted a token that belongs to a different Pixel.
        if (!empty($granular_target_ids) && !in_array((string) $pixel_id, $granular_target_ids, true)) {
            wp_send_json_error(array(
                'state'      => 'wrong_dataset_token',
                'message'    => sprintf(
                    /* translators: %1$s is the dataset ID(s) the token belongs to, %2$s is the Pixel ID the user entered */
                    __('This token belongs to a different dataset (ID %1$s), not to Pixel %2$s. Open Events Manager for Pixel %2$s and generate the access token from there, or correct the Pixel ID above.', 'unipixel'),
                    implode(', ', $granular_target_ids),
                    $pixel_id
                ),
                'meta_error' => $events_body['error'],
            ));
            return;
        }

        $err      = $events_body['error'];
        $err_code = isset($err['code']) ? (int) $err['code'] : 0;
        // Code 100 on the events call means the token cannot post to this Pixel
        // (wrong Pixel ID, or the token was never granted this Pixel).
        if ($err_code === 100) {
            wp_send_json_error(array(
                'state'      => 'no_pixel_access',
                'message'    => sprintf(
                    /* translators: %s is the Meta Pixel ID */
                    __('Token could not send events to Pixel %s. Either the Pixel ID is wrong, or the token was not granted access to this Pixel. Generate the token from this Pixel\'s own settings in Events Manager, under Settings then Conversions API.', 'unipixel'),
                    $pixel_id
                ),
                'meta_error' => $err,
            ));
            return;
        }
        $diagnosis = unipixel_diagnose_meta_error($err);
        wp_send_json_error(array(
            'state'      => $diagnosis['state'],
            'message'    => $diagnosis['message'],
            'meta_error' => $err,
        ));
        return;
    }

    $events_received = isset($events_body['events_received']) ? (int) $events_body['events_received'] : 0;
    if ($events_received < 1) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => __('Meta accepted the request but did not confirm the test event. Try again.', 'unipixel'),
        ));
        return;
    }

    // Test event delivered. Record the timestamp for the persistent status indicator.
    $timestamps = get_option('unipixel_test_connection_timestamps', array());
    if (!is_array($timestamps)) {
        $timestamps = array();
    }
    $timestamps[1] = time();
    update_option('unipixel_test_connection_timestamps', $timestamps, false);

    wp_send_json_success(array(
        'state'      => 'connected',
        'message'    => sprintf(
            /* translators: %1$s is the Meta Pixel ID, %2$s is the test event code */
            __('Connected. A test event was delivered to Pixel %1$s through the Conversions API. You can see it in Events Manager under Test events, code %2$s.', 'unipixel'),
            $pixel_id,
            $test_event_code
        ) . $expiry_warning,
        'pixel_id'   => $pixel_id,
        'expires_at' => $expires_at,
        'scopes'     => $scopes,
    ));
}

/**
 * Translate a Meta Graph API error block into a Test Connection state + actionable message.
 *
 * Meta error code reference (loose, current as of 2026-05-13):
 *   - 190: OAuth access token issue (subcodes 458/459/460/463/464/467 specialise)
 *   - 100: Invalid parameter (often: wrong Pixel ID, or no access to the Pixel)
 *   - 200/220: Permission denied (missing scope or asset access)
 *   - 10/4/17: Rate limit / app limit
 *   - 230: Permissions disabled by user
 *
 * @param array $err The Meta error block: {code, message, type, error_subcode, ...}
 * @return array { state: string, message: string }
 */
function unipixel_diagnose_meta_error($err)
{
    $code    = isset($err['code']) ? (int) $err['code'] : 0;
    $subcode = isset($err['error_subcode']) ? (int) $err['error_subcode'] : 0;
    $raw_msg = isset($err['message']) ? $err['message'] : '';

    if ($code === 190) {
        if ($subcode === 460) {
            return array(
                'state'   => 'token_revoked',
                'message' => __('Your Facebook password was changed since this token was generated, so Meta has revoked the token. Generate a new long-lived System User token in Events Manager.', 'unipixel'),
            );
        }
        if ($subcode === 463 || $subcode === 467) {
            return array(
                'state'   => 'token_expired',
                'message' => __('Your access token has expired. Generate a new token in Events Manager (use a long-lived System User token to avoid future expiry).', 'unipixel'),
            );
        }
        if ($subcode === 458) {
            return array(
                'state'   => 'token_revoked',
                'message' => __('The app or user associated with this token has been removed or uninstalled. Generate a new token from a valid system user.', 'unipixel'),
            );
        }
        if ($subcode === 459) {
            return array(
                'state'   => 'token_revoked',
                'message' => __('The user account associated with this token requires a security checkpoint with Meta. Log into facebook.com to clear it, then regenerate the token.', 'unipixel'),
            );
        }
        return array(
            'state'   => 'invalid_token',
            'message' => __('Token is invalid or expired. Regenerate it in Events Manager.', 'unipixel'),
        );
    }

    if ($code === 200 || $code === 220) {
        return array(
            'state'   => 'token_missing_scope',
            'message' => __('Token is missing the permission needed for this operation, typically "ads_management". Regenerate the token and make sure that permission is granted.', 'unipixel'),
        );
    }

    if ($code === 100) {
        return array(
            'state'   => 'invalid_parameter',
            'message' => __('Meta rejected the request as invalid. Most commonly this means the Pixel ID is wrong, or the token does not have access to this Pixel.', 'unipixel')
                . ($raw_msg !== '' ? ' ' . __('Meta said:', 'unipixel') . ' ' . $raw_msg : ''),
        );
    }

    if ($code === 4 || $code === 17 || $code === 10) {
        return array(
            'state'   => 'rate_limited',
            'message' => __('Meta is rate-limiting requests right now. Wait a few minutes and try again.', 'unipixel'),
        );
    }

    if ($code === 230) {
        return array(
            'state'   => 'permissions_disabled',
            'message' => __('The user has disabled some permissions on the token. Regenerate it with the required permissions.', 'unipixel'),
        );
    }

    // Generic fallback - keep Meta's raw message available so the user has a string to search.
    return array(
        'state'   => 'invalid_token',
        'message' => $raw_msg !== ''
            ? sprintf(/* translators: %s is the raw error message Meta returned */ __('Meta returned an error: %s', 'unipixel'), $raw_msg)
            : __('Meta returned an unrecognised error. Try regenerating your access token.', 'unipixel'),
    );
}
