<?php

//File: public_html\wp-content\plugins\unipixel\trackers\microsoft-handler.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function unipixel_receive_ajax_data_for_server_event_microsoft()
{
    // Verify nonce
    check_ajax_referer('unipixel_track_nonce', 'nonce');

    $platformId = 5;
    $platformSettings = unipixel_get_platform_settings($platformId);

    // Early exit if server-side tracking is not enabled for this platform
    $event_name_raw = sanitize_text_field(wp_unslash($_POST['eventName']));
    if (empty($platformSettings->serverside_global_enabled)) {
        echo wp_json_encode([$event_name_raw => 'Server-side not enabled for platform, skipped']);
        wp_die();
    }

    // Sanitize input parameters
    $event_name    = $event_name_raw;
    $element_ref   = sanitize_text_field(wp_unslash($_POST['elementRef']));
    $event_trigger = sanitize_text_field(wp_unslash($_POST['eventTrigger']));
    $event_id      = sanitize_text_field(wp_unslash($_POST['event_id']));
    $pageUrl       = isset($_POST['pageUrl']) ? esc_url_raw(wp_unslash($_POST['pageUrl'])) : '';

    $isPageView = ($event_name === 'PageView');

    $sendServer = false;
    $sendServerLogResponse = false;

    if ($isPageView) {
        $sendServer = ! empty($platformSettings->pageview_send_serverside) ? intval($platformSettings->pageview_send_serverside) : 0;
        $sendServerLogResponse = ! empty($platformSettings->send_server_log_response) ? intval($platformSettings->send_server_log_response) : 0;
    } else {
        $eventSettings = unipixel_get_event_settings($platformId, $event_name);
        $sendServer = intval($eventSettings->send_server ?? 0);
        $sendServerLogResponse = intval($eventSettings->send_server_log_response ?? 0);
    }

    if (!$sendServer) {
        echo wp_json_encode([$event_name => 'Server event not enabled, skipped']);
        wp_die();
    }

    $eventTime = time();
    $msclkid_value = unipixel_get_msclkid_value();

    // Prepare user data for Microsoft CAPI
    $user_data = [
        'clientIpAddress' => sanitize_text_field(unipixel_get_ip_address()),
        'clientUserAgent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ];

    if (!empty($msclkid_value)) {
        $user_data['msclkid'] = sanitize_text_field($msclkid_value);
    }

    // Advanced Matching — merge hashed PII from logged-in user (Microsoft format: string values)
    $amData = unipixel_get_advanced_matching_data();
    $microsoft_am_map = [
        'plcehldr_hashedEmail' => 'em',
        'plcehldr_hashedPhone' => 'ph',
    ];
    foreach ($microsoft_am_map as $placeholder => $apiKey) {
        if (!empty($amData[$placeholder])) {
            $user_data[$apiKey] = $amData[$placeholder];
        }
    }

    $sanitized_custom_data = []; // Reserved for future extension

    $sendServerResultInfo = unipixel_send_server_event_microsoft(
        $event_name,
        $user_data,
        $sanitized_custom_data,
        $event_id,
        $eventTime,
        $pageUrl,
        $sendServerLogResponse
    );

    $jsonRtn = unipixel_handle_send_event_result($platformId, $element_ref, $event_trigger, $event_name, $sendServerResultInfo, "server", "first", "serverSecond");

    echo $jsonRtn;
    wp_die();
}

add_action('wp_ajax_unipixel_ajax_server_event_microsoft', 'unipixel_receive_ajax_data_for_server_event_microsoft');
add_action('wp_ajax_nopriv_unipixel_ajax_server_event_microsoft', 'unipixel_receive_ajax_data_for_server_event_microsoft');
