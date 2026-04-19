<?php

//File: public_html\wp-content\plugins\unipixel\trackers\google-ajax-listener-send-server.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


// Handles the AJAX request, verifies the nonce, processes the event data, and logs the event
function unipixel_receive_ajax_data_for_server_event_google()
{
    // Verify nonce
    check_ajax_referer('unipixel_track_nonce', 'nonce');

    $platformId = 4;
    $platformSettings = unipixel_get_platform_settings($platformId);

    // Early exit if server-side tracking is not enabled for this platform
    $event_name_raw = sanitize_text_field($_POST['eventName']);
    if (empty($platformSettings->serverside_global_enabled)) {
        echo wp_json_encode([$event_name_raw => 'Server-side not enabled for platform, skipped']);
        wp_die();
    }

    $event_name         = $event_name_raw;
    $element_ref        = sanitize_text_field($_POST['elementRef']);
    $event_trigger      = sanitize_text_field($_POST['eventTrigger']);
    $event_id           = sanitize_text_field($_POST['event_id']);

    $isPageView = ($event_name === 'page_view');

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

    $gclid = unipixel_get_gclid_value();


    $sanitized_custom_data = [];

    // Pull the raw eventParams once:
    $rawParams = isset($_POST['eventParams']) && is_array($_POST['eventParams']) ? $_POST['eventParams'] : [];

    // If the client passed us page_location, engagement_time_msec, sanitize & add them:
    if (! empty($rawParams['page_location'])) {
        $sanitized_custom_data['page_location'] = esc_url_raw(wp_unslash($rawParams['page_location']));
    }
    if (isset($rawParams['engagement_time_msec'])) {
        $sanitized_custom_data['engagement_time_msec'] = intval($rawParams['engagement_time_msec']);
    }

    if (!empty($gclid)) {
        $sanitized_custom_data["gclid"] = $gclid;
    }

    // Prepare user data
    $postedClientId = sanitize_text_field($_POST['googleClientId'] ?? '');
    $cookieClientId = $_COOKIE['_ga'] ?? '';

    $normalizedClientId = unipixel_normalize_ga_client_id($postedClientId);
    if ($normalizedClientId === '') {
        $normalizedClientId = unipixel_normalize_ga_client_id($cookieClientId);
    }

    $user_data = [
        'client_ip_address' => sanitize_text_field(unipixel_get_ip_address()),
        'client_user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'client_id'         => sanitize_text_field($normalizedClientId),
    ];


    $sendServerResultInfo = unipixel_send_server_event_google($event_name, $user_data, $sanitized_custom_data, $event_id, $sendServerLogResponse);

    $jsonRtn = unipixel_handle_send_event_result($platformId, $element_ref, $event_trigger, $event_name, $sendServerResultInfo,  "server", "first", "serverSecond");

    echo $jsonRtn;

    wp_die();
}

add_action('wp_ajax_ajax_data_for_server_event_google', 'unipixel_receive_ajax_data_for_server_event_google');
add_action('wp_ajax_nopriv_ajax_data_for_server_event_google', 'unipixel_receive_ajax_data_for_server_event_google');
