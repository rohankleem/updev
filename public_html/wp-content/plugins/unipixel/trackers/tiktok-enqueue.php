<?php

//File: public_html\wp-content\plugins\unipixel\trackers\tiktok-enqueue.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Enqueue the TikTok Pixel base script if enabled.
 */
function unipixel_enqueue_pixel_tiktok()
{
    global $wpdb;
    $platform_id = 3; // TikTok platform ID
    $table_name  = $wpdb->prefix . 'unipixel_platform_settings';

    // Retrieve platform settings
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );
    $platform_settings = $wpdb->get_row($query, ARRAY_A);

    if (!$platform_settings || empty($platform_settings['platform_enabled'])) {
        return;
    }

    $tiktok_pixel_id = esc_js($platform_settings['pixel_id']);
    $pixel_setting   = esc_js($platform_settings['pixel_setting']);

    // Include TikTok Pixel only if user selected "Include"
    if ($pixel_setting === 'include' && !empty($tiktok_pixel_id)) {
        wp_enqueue_script(
            'unipixel-pixel-tiktok',
            plugin_dir_url(__DIR__) . 'js/pixel-tiktok.js',
            [],
            UNIPIXEL_VERSION,
            false
        );

        // Build client-side AM data for TikTok (ttq.identify supports email, phone_number)
        $am_client = [];
        $amData = unipixel_get_advanced_matching_data();
        if (!empty($amData['plcehldr_hashedEmail'])) {
            $am_client['email'] = $amData['plcehldr_hashedEmail'];
        }
        if (!empty($amData['plcehldr_hashedPhone'])) {
            $am_client['phone_number'] = $amData['plcehldr_hashedPhone'];
        }

        wp_localize_script('unipixel-pixel-tiktok', 'tiktokPixelSettings', [
            'pixel_id' => $tiktok_pixel_id,
            'advanced_matching' => $am_client,
        ]);
    }
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_pixel_tiktok', 10);


/**
 * Enqueue TikTok event tracking script (client-first).
 */
function unipixel_enqueue_scripts_tiktok()
{
    global $wpdb;
    $platform_id = 3; // TikTok
    $table_name  = $wpdb->prefix . 'unipixel_platform_settings';

    // Get TikTok settings
    $query = $wpdb->prepare("SELECT * FROM %i WHERE id = %d", $table_name, $platform_id);
    $platformSettings = $wpdb->get_row($query, ARRAY_A);

    $platformEnabled  = !empty($platformSettings['platform_enabled']);
    $tiktokPixelId    = isset($platformSettings['pixel_id']) ? esc_js($platformSettings['pixel_id']) : '';

    if (!$platformEnabled || !$tiktokPixelId) {
        wp_add_inline_script('unipixel-common', "
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'TikTok | Platform tracking disabled -- event tracking will not run.');
            }
        ");
        return;
    }

    // Retrieve event list for TikTok
    $events = unipixel_get_events_for_platform($platform_id);

    $eventsToTrack = array_map(function ($event) {
        return [
            'elementRef'                => $event['element_ref'],
            'trigger'                   => $event['event_trigger'],
            'name'                      => $event['event_name'],
            'send_client'               => (int) ($event['send_client'] ?? 0),
            'send_server'               => (int) ($event['send_server'] ?? 0),
            'send_server_log_response'  => (int) ($event['send_server_log_response'] ?? 0),
        ];
    }, $events);

    // Add default PageView event
    array_unshift($eventsToTrack, [
        'elementRef' => 'body',
        'trigger'    => 'shown',
        'name'       => 'PageView',
        'send_client'              => (int) ($platformSettings['pageview_send_clientside'] ?? 0),
        'send_server'              => (int) ($platformSettings['pageview_send_serverside'] ?? 0),
        'send_server_log_response' => (int) ($platformSettings['send_server_log_response'] ?? 0),
    ]);

    // Enqueue TikTok client-first script
    $script_url = plugin_dir_url(__DIR__) . 'js/clientfirst-watch-and-send-tiktok.js';

    wp_enqueue_script(
        'clientfirst-watch-and-send-tiktok',
        $script_url,
        ['unipixel-common', 'unipixel-console-logger'],
        UNIPIXEL_VERSION,
        true
    );

    wp_localize_script('clientfirst-watch-and-send-tiktok', 'UniPixelEventDataTikTok', [
        'eventsToTrack' => $eventsToTrack,
        'serverside_global_enabled' => (int) ($platformSettings['serverside_global_enabled'] ?? 0),
        'ajaxurl'       => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('unipixel_track_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_scripts_tiktok', 20);
