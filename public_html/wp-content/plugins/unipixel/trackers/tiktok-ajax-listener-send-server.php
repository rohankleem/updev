<?php

//File: public_html/wp-content/plugins/unipixel/trackers/tiktok-ajax-listener-send-server.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function unipixel_receive_ajax_data_for_server_event_tiktok() {

    // 1. Verify nonce for security
    check_ajax_referer('unipixel_track_nonce', 'nonce');

    $platformId = 3; // TikTok
    $platformSettings = unipixel_get_platform_settings($platformId);

    // Early exit if server-side tracking is not enabled for this platform
    $event_name_raw = sanitize_text_field($_POST['eventName'] ?? '');
    if (empty($platformSettings->serverside_global_enabled)) {
        echo wp_json_encode([$event_name_raw => 'Server-side not enabled for platform, skipped']);
        wp_die();
    }

    // 2. Sanitize input parameters
    $event_name    = $event_name_raw;
    $element_ref   = sanitize_text_field($_POST['elementRef'] ?? '');
    $event_trigger = sanitize_text_field($_POST['eventTrigger'] ?? '');
    $event_id      = sanitize_text_field($_POST['event_id'] ?? '');
    $pageUrl       = isset($_POST['pageUrl']) ? esc_url_raw(wp_unslash($_POST['pageUrl'])) : '';

    $isPageView = ($event_name === 'PageView');

    // 3. Determine whether to send the server event
    $sendServer            = false;
    $sendServerLogResponse = false;

    if ($isPageView) {
        $sendServer            = !empty($platformSettings->pageview_send_serverside) ? intval($platformSettings->pageview_send_serverside) : 0;
        $sendServerLogResponse = !empty($platformSettings->send_server_log_response) ? intval($platformSettings->send_server_log_response) : 0;
    } else {
        $eventSettings         = unipixel_get_event_settings($platformId, $event_name);
        $sendServer            = intval($eventSettings->send_server ?? 0);
        $sendServerLogResponse = intval($eventSettings->send_server_log_response ?? 0);
    }

    if (!$sendServer) {
        echo wp_json_encode([$event_name => 'Server event not enabled, skipped']);
        wp_die();
    }

    // 4. Build common user + custom data
    $eventTime = time();
    $ip        = sanitize_text_field(unipixel_get_ip_address());
    $userAgent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

    // Retrieve TikTok cookie & click data
    $ttData = unipixel_get_tt_value();
    $ttp_cookie = sanitize_text_field($ttData['ttp_cookie'] ?? '');
    $ttclid     = sanitize_text_field($ttData['ttclid'] ?? '');

    // User data (context.user) — keys must match TikTok Events API field names
    $user_data = [
        'ip'          => $ip,
        'user_agent'  => $userAgent,
        'ttp'         => $ttp_cookie,
        'ttclid'      => $ttclid,
    ];

    // Advanced Matching — merge hashed PII from logged-in user (TikTok format: string values)
    $amData = unipixel_get_advanced_matching_data();
    $tiktok_am_map = [
        'plcehldr_hashedEmail'   => 'email',
        'plcehldr_hashedPhone'   => 'phone',
        'plcehldr_hashedFn'      => 'first_name',
        'plcehldr_hashedLn'      => 'last_name',
        'plcehldr_hashedCt'      => 'city',
        'plcehldr_hashedSt'      => 'state',
        'plcehldr_hashedZp'      => 'zip_code',
        'plcehldr_hashedCountry' => 'country',
    ];
    foreach ($tiktok_am_map as $placeholder => $apiKey) {
        if (!empty($amData[$placeholder])) {
            $user_data[$apiKey] = $amData[$placeholder];
        }
    }

    // Custom data (properties)
    $sanitized_custom_data = [];

    // 5. Send to TikTok
    $sendServerResultInfo = unipixel_send_server_event_tiktok(
        $event_name,
        $user_data,
        $sanitized_custom_data,
        $event_id,
        $eventTime,
        $pageUrl,
        $sendServerLogResponse
    );

    // 6. Standard UniPixel JSON response (logs + returns)
    $jsonRtn = unipixel_handle_send_event_result(
        $platformId,
        $element_ref,
        $event_trigger,
        $event_name,
        $sendServerResultInfo,
        'server',
        'first',
        'serverSecond'
    );

    echo $jsonRtn;
    wp_die();
}

add_action('wp_ajax_ajax_data_for_server_event_tiktok', 'unipixel_receive_ajax_data_for_server_event_tiktok');
add_action('wp_ajax_nopriv_ajax_data_for_server_event_tiktok', 'unipixel_receive_ajax_data_for_server_event_tiktok');
