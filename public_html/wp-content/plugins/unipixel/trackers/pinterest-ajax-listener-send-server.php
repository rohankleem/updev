<?php

// File: public_html\wp-content\plugins\unipixel\trackers\pinterest-ajax-listener-send-server.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function unipixel_receive_ajax_data_for_server_event_pinterest()
{
    // Verify nonce
    check_ajax_referer('unipixel_track_nonce', 'nonce');

    $platformId = 2;
    $platformSettings = unipixel_get_platform_settings($platformId);

    // Early exit if server-side tracking is not enabled for this platform
    $event_name_raw = sanitize_text_field($_POST['eventName']);
    if (empty($platformSettings->serverside_global_enabled)) {
        echo wp_json_encode([$event_name_raw => 'Server-side not enabled for platform, skipped']);
        wp_die();
    }

    // Sanitize input parameters
    $event_name    = $event_name_raw;
    $element_ref   = sanitize_text_field($_POST['elementRef']);
    $event_trigger = sanitize_text_field($_POST['eventTrigger']);
    $event_id      = sanitize_text_field($_POST['event_id']);
    $pageUrl       = isset($_POST['pageUrl']) ? esc_url_raw(wp_unslash($_POST['pageUrl'])) : '';

    $isPageView = ($event_name === 'PageView');

    // Map client-first event names to Pinterest server API names
    $pinterest_event_name_map = [
        'PageView'         => 'page_visit',
        'pagevisit'        => 'page_visit',
        'addtocart'        => 'add_to_cart',
        'checkout'         => 'checkout',
        'initiatecheckout' => 'initiate_checkout',
        'viewcontent'      => 'view_content',
        'search'           => 'search',
        'lead'             => 'lead',
        'signup'           => 'signup',
    ];
    $pinterest_api_event_name = isset($pinterest_event_name_map[$event_name])
        ? $pinterest_event_name_map[$event_name]
        : $event_name; // pass through custom event names as-is

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

    // Prepare user data with Pinterest cookies
    $user_data = [
        'client_ip_address' => sanitize_text_field(unipixel_get_ip_address()),
        'client_user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ];

    // _epik cookie (Pinterest click ID)
    $epik_value = sanitize_text_field($_COOKIE['_epik'] ?? '');
    if (!empty($epik_value)) {
        $user_data['click_id'] = $epik_value;
    }

    // Advanced Matching — merge hashed PII from logged-in user (Pinterest format: array-wrapped values)
    $amData = unipixel_get_advanced_matching_data();
    $pinterest_am_map = [
        'plcehldr_hashedEmail'   => 'em',
        'plcehldr_hashedPhone'   => 'ph',
        'plcehldr_hashedFn'      => 'fn',
        'plcehldr_hashedLn'      => 'ln',
        'plcehldr_hashedCt'      => 'ct',
        'plcehldr_hashedSt'      => 'st',
        'plcehldr_hashedZp'      => 'zp',
        'plcehldr_hashedCountry' => 'country',
    ];
    foreach ($pinterest_am_map as $placeholder => $apiKey) {
        if (!empty($amData[$placeholder])) {
            $user_data[$apiKey] = [$amData[$placeholder]];
        }
    }

    $sanitized_custom_data = []; // Reserved for future extension

    $sendServerResultInfo = unipixel_send_server_event_pinterest($pinterest_api_event_name, $user_data, $sanitized_custom_data, $event_id, $eventTime, $pageUrl, $sendServerLogResponse);

    $jsonRtn = unipixel_handle_send_event_result($platformId, $element_ref, $event_trigger, $event_name, $sendServerResultInfo, "server", "first", "serverSecond");

    echo $jsonRtn;
    wp_die();
}

add_action('wp_ajax_ajax_data_for_server_event_pinterest', 'unipixel_receive_ajax_data_for_server_event_pinterest');
add_action('wp_ajax_nopriv_ajax_data_for_server_event_pinterest', 'unipixel_receive_ajax_data_for_server_event_pinterest');
