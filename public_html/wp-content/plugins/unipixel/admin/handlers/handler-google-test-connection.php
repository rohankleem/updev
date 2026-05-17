<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for Test Connection on the Google setup page.
 *
 * Important Google-specific caveat: Google's Measurement Protocol debug
 * endpoint deliberately does NOT validate the api_secret or measurement_id
 * values. It only validates event payload structure. So this handler can
 * confirm:
 *   - measurement_id is in the right format (G-XXXXXXXXXX)
 *   - api_secret is non-empty
 *   - a sample test event is accepted as well-formed by Google's debug endpoint
 * It cannot confirm the api_secret value is correct for this property. The
 * success message surfaces that limitation to the user honestly.
 */

add_action('wp_ajax_unipixel_google_test_connection', 'unipixel_handle_google_test_connection');

function unipixel_handle_google_test_connection()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'state'   => 'forbidden',
            'message' => __('You do not have permission to run this action.', 'unipixel'),
        ), 403);
        return;
    }

    $measurement_id = isset($_POST['pixel_id']) ? sanitize_text_field(wp_unslash($_POST['pixel_id'])) : '';
    $api_secret     = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';

    if ($measurement_id === '') {
        wp_send_json_error(array(
            'state'   => 'no_measurement_id',
            'message' => __('No Measurement ID entered. Paste your Google Measurement ID above first.', 'unipixel'),
        ));
        return;
    }

    if ($api_secret === '') {
        wp_send_json_error(array(
            'state'   => 'no_api_secret',
            'message' => __('No API Secret entered. Paste your Measurement Protocol API Secret above first.', 'unipixel'),
        ));
        return;
    }

    // GA4 Measurement IDs start with "G-" and contain uppercase letters or digits.
    if (!preg_match('/^G-[A-Z0-9]+$/', $measurement_id)) {
        wp_send_json_error(array(
            'state'   => 'invalid_measurement_id_format',
            'message' => __('Measurement ID looks wrong. It should start with "G-" followed by uppercase letters or digits (e.g. G-XXXXXXXXXX).', 'unipixel'),
        ));
        return;
    }

    // Format check on API Secret. Real GA4 Measurement Protocol API Secrets are
    // ~22 characters of URL-safe base64 (A-Z, a-z, 0-9, _, -). Allowing 15-40
    // to be tolerant of any unusual cases while still catching obvious typos
    // and fake test values like "test123".
    if (!preg_match('/^[A-Za-z0-9_\-]{15,40}$/', $api_secret)) {
        wp_send_json_error(array(
            'state'   => 'invalid_api_secret_format',
            'message' => __('API Secret looks wrong. Real Google API Secrets are around 22 characters made of letters, digits, underscores, and hyphens. Did you paste the full secret?', 'unipixel'),
        ));
        return;
    }

    // Build the test event payload. debug_mode: 1 makes the event appear in
    // GA4 DebugView (the user's self-verification path), not in regular reports.
    $client_id     = 'unipixel.test_connection.' . wp_generate_password(10, false, false) . '.' . time();
    $event_payload = array(
        'client_id' => $client_id,
        'events'    => array(array(
            'name'   => 'unipixel_test_connection',
            'params' => array(
                'debug_mode' => 1,
            ),
        )),
    );
    $body = wp_json_encode($event_payload);

    // Step 1: send to debug endpoint to validate payload structure.
    // Endpoint: /debug/mp/collect (no underscores). Verified 2026-05-13 via direct curl.
    $debug_url = 'https://www.google-analytics.com/debug/mp/collect?' . http_build_query(array(
        'measurement_id' => $measurement_id,
        'api_secret'     => $api_secret,
    ));

    $debug_response = wp_remote_post($debug_url, array(
        'timeout' => 10,
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => $body,
    ));

    if (is_wp_error($debug_response)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Google. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $debug_response->get_error_message(),
        ));
        return;
    }

    $debug_body = json_decode(wp_remote_retrieve_body($debug_response), true);

    if (!is_array($debug_body) || !isset($debug_body['validationMessages'])) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => __('Google returned an unexpected response from the debug endpoint. Try again.', 'unipixel'),
        ));
        return;
    }

    if (!empty($debug_body['validationMessages'])) {
        $first       = $debug_body['validationMessages'][0];
        $description = isset($first['description']) ? $first['description'] : __('Validation failed.', 'unipixel');
        wp_send_json_error(array(
            'state'         => 'validation_failed',
            'message'       => __('Event validation failed: ', 'unipixel') . $description,
            'google_errors' => $debug_body['validationMessages'],
        ));
        return;
    }

    // Step 2: send the same event to the production endpoint so it lands in
    // GA4 DebugView. This is what gives the user a self-verification path for
    // whether the API Secret value is actually correct (Google does not
    // surface auth errors anywhere we can read directly).
    $prod_url = 'https://www.google-analytics.com/mp/collect?' . http_build_query(array(
        'measurement_id' => $measurement_id,
        'api_secret'     => $api_secret,
    ));

    $prod_response = wp_remote_post($prod_url, array(
        'timeout' => 10,
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => $body,
    ));

    if (is_wp_error($prod_response)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Google\'s production endpoint. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $prod_response->get_error_message(),
        ));
        return;
    }

    $prod_code = (int) wp_remote_retrieve_response_code($prod_response);
    if ($prod_code !== 204 && $prod_code !== 200) {
        wp_send_json_error(array(
            'state'   => 'production_endpoint_error',
            'message' => sprintf(
                /* translators: %d is the HTTP status code Google returned */
                __('Google production endpoint returned an unexpected status (HTTP %d). Try again.', 'unipixel'),
                $prod_code
            ),
        ));
        return;
    }

    // All checks passed. Record the timestamp.
    $timestamps = get_option('unipixel_test_connection_timestamps', array());
    if (!is_array($timestamps)) {
        $timestamps = array();
    }
    $timestamps[4] = time();
    update_option('unipixel_test_connection_timestamps', $timestamps, false);

    wp_send_json_success(array(
        'state'   => 'connected',
        'message' => __('Format checks passed. A test event has been sent to Google with debug_mode on. To confirm your API Secret value is actually correct, open Google Analytics → Admin → DebugView and look for the event "unipixel_test_connection" within 60 seconds. If it appears, you are fully set up. If it does not appear, the API Secret is likely wrong (Google does not surface this error to us directly).', 'unipixel'),
    ));
}
