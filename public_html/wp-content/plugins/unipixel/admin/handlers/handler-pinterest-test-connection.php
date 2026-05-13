<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for Test Connection on the Pinterest setup page.
 *
 * Pinterest needs three credentials: Tag ID (in pixel_id column), Ad Account ID
 * (in additional_id column), and Access Token (in access_token column). The
 * handler validates each in turn.
 *
 * Two-call validation strategy (verified by direct curl 2026-05-13):
 *   - GET /v5/user_account with Bearer token -> 401 means invalid token,
 *                                               200 means token is valid.
 *   - GET /v5/ad_accounts/{ad_account_id}    -> 200 means token has access,
 *                                               403/404 means no access or wrong ID.
 *
 * Pinterest API base: https://api.pinterest.com/v5/
 * Auth: Authorization: Bearer <access_token>
 */

add_action('wp_ajax_unipixel_pinterest_test_connection', 'unipixel_handle_pinterest_test_connection');

function unipixel_handle_pinterest_test_connection()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    $tag_id        = isset($_POST['pixel_id']) ? sanitize_text_field(wp_unslash($_POST['pixel_id'])) : '';
    $ad_account_id = isset($_POST['additional_id']) ? sanitize_text_field(wp_unslash($_POST['additional_id'])) : '';
    $access_token  = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';

    if ($tag_id === '') {
        wp_send_json_error(array(
            'state'   => 'no_tag_id',
            'message' => __('No Pinterest Tag ID entered. Paste your Pinterest Tag ID above first.', 'unipixel'),
        ));
        return;
    }

    if ($ad_account_id === '') {
        wp_send_json_error(array(
            'state'   => 'no_ad_account_id',
            'message' => __('No Ad Account ID entered. Paste your Pinterest Ad Account ID above first.', 'unipixel'),
        ));
        return;
    }

    if ($access_token === '') {
        wp_send_json_error(array(
            'state'   => 'no_token',
            'message' => __('No Conversion Access Token entered. Paste your Pinterest Conversion Access Token above first.', 'unipixel'),
        ));
        return;
    }

    // Format check: Pinterest Tag IDs are numeric strings, typically 13 digits.
    if (!preg_match('/^\d{10,20}$/', $tag_id)) {
        wp_send_json_error(array(
            'state'   => 'invalid_tag_id_format',
            'message' => __('Pinterest Tag ID looks wrong. Pinterest Tag IDs are numeric (digits only), typically around 13 digits. Make sure you are not mixing up another platform\'s ID.', 'unipixel'),
        ));
        return;
    }

    // Format check: Pinterest Ad Account IDs are numeric strings, typically 12-13 digits.
    if (!preg_match('/^\d{8,20}$/', $ad_account_id)) {
        wp_send_json_error(array(
            'state'   => 'invalid_ad_account_id_format',
            'message' => __('Ad Account ID looks wrong. Pinterest Ad Account IDs are numeric (digits only), typically around 12-13 digits. Find yours in Pinterest Ads Manager URL or under Business → Ad accounts.', 'unipixel'),
        ));
        return;
    }

    // Format check: Pinterest Conversion Access Tokens are long alphanumeric (often
    // prefixed with "pina_" in newer versions). Allow either format, 30+ chars.
    if (!preg_match('/^(pina_)?[A-Za-z0-9_\-]{30,200}$/', $access_token)) {
        wp_send_json_error(array(
            'state'   => 'invalid_token_format',
            'message' => __('Conversion Access Token looks wrong. Pinterest tokens are 30+ characters of letters, digits, underscores or hyphens (newer tokens start with "pina_"). Did you paste the full token?', 'unipixel'),
        ));
        return;
    }

    // Step 1: validate the token via /v5/user_account.
    // 401 = invalid token. 200 = valid (returns user info we don't strictly need).
    $user_resp = wp_remote_get('https://api.pinterest.com/v5/user_account', array(
        'timeout' => 10,
        'headers' => array('Authorization' => 'Bearer ' . $access_token),
    ));

    if (is_wp_error($user_resp)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Pinterest. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $user_resp->get_error_message(),
        ));
        return;
    }

    $user_code = (int) wp_remote_retrieve_response_code($user_resp);

    if ($user_code === 401) {
        wp_send_json_error(array(
            'state'   => 'invalid_token',
            'message' => __('Pinterest rejected the access token (HTTP 401). The token is invalid, revoked, or expired. Regenerate it in Pinterest Ads Manager → Conversions → Access Tokens.', 'unipixel'),
        ));
        return;
    }

    if ($user_code === 403) {
        wp_send_json_error(array(
            'state'   => 'token_missing_scope',
            'message' => __('Pinterest rejected the request (HTTP 403). The token is valid but does not have the required scope. Regenerate it with read_users and ad_account scopes (the Pinterest Conversion Access Token type covers these by default).', 'unipixel'),
        ));
        return;
    }

    if ($user_code === 429) {
        wp_send_json_error(array(
            'state'   => 'rate_limited',
            'message' => __('Pinterest is rate-limiting requests right now (HTTP 429). Wait a few minutes and try again.', 'unipixel'),
        ));
        return;
    }

    if ($user_code !== 200) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => sprintf(
                /* translators: %d is the HTTP status code Pinterest returned */
                __('Pinterest returned an unexpected status (HTTP %d) when validating the token. Try again.', 'unipixel'),
                $user_code
            ),
        ));
        return;
    }

    // Step 2: confirm the token has access to the pasted Ad Account.
    $account_url = 'https://api.pinterest.com/v5/ad_accounts/' . rawurlencode($ad_account_id);
    $account_resp = wp_remote_get($account_url, array(
        'timeout' => 10,
        'headers' => array('Authorization' => 'Bearer ' . $access_token),
    ));

    if (is_wp_error($account_resp)) {
        wp_send_json_error(array(
            'state'   => 'network_error',
            'message' => __('Could not reach Pinterest on the Ad Account check. Check your internet connection and try again.', 'unipixel'),
            'detail'  => $account_resp->get_error_message(),
        ));
        return;
    }

    $account_code = (int) wp_remote_retrieve_response_code($account_resp);
    $account_body = json_decode(wp_remote_retrieve_body($account_resp), true);

    if ($account_code === 404) {
        wp_send_json_error(array(
            'state'   => 'no_ad_account_access',
            'message' => sprintf(
                /* translators: %s is the Pinterest Ad Account ID */
                __('Pinterest could not find Ad Account %s, or this token does not have access to it. Double-check the Ad Account ID in Pinterest Ads Manager URL, and that the token was generated for an account that can access this Ad Account.', 'unipixel'),
                $ad_account_id
            ),
        ));
        return;
    }

    if ($account_code === 403) {
        wp_send_json_error(array(
            'state'   => 'no_ad_account_access',
            'message' => sprintf(
                /* translators: %s is the Pinterest Ad Account ID */
                __('Token does not have permission to access Ad Account %s. The Pinterest user who generated this token must have a role on this Ad Account (Admin or Editor).', 'unipixel'),
                $ad_account_id
            ),
        ));
        return;
    }

    if ($account_code !== 200) {
        wp_send_json_error(array(
            'state'   => 'unexpected_response',
            'message' => sprintf(
                /* translators: %d is the HTTP status code Pinterest returned */
                __('Pinterest returned an unexpected status (HTTP %d) on the Ad Account check. Try again.', 'unipixel'),
                $account_code
            ),
        ));
        return;
    }

    // Both checks passed. Record the timestamp.
    $timestamps = get_option('unipixel_test_connection_timestamps', array());
    if (!is_array($timestamps)) {
        $timestamps = array();
    }
    $timestamps[2] = time(); // platform_id 2 = Pinterest
    update_option('unipixel_test_connection_timestamps', $timestamps, false);

    $account_name = is_array($account_body) && isset($account_body['name']) ? $account_body['name'] : '';
    $display      = ($account_name !== '') ? $account_name : $ad_account_id;

    wp_send_json_success(array(
        'state'         => 'connected',
        'message'       => sprintf(
            /* translators: %s is the Pinterest Ad Account name or ID */
            __('Connected. Token is valid and has access to Ad Account "%s". Note: this verifies token + ad-account access. To confirm tag-level event delivery, fire a real event and check Pinterest Ads Manager → Conversions → your Tag → Event activity.', 'unipixel'),
            $display
        ),
        'account_id'    => $ad_account_id,
        'account_name'  => $account_name,
    ));
}
