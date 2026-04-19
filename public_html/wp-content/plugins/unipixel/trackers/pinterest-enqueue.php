<?php

// File: public_html\wp-content\plugins\unipixel\trackers\pinterest-enqueue.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueues the Pinterest Tag script if the platform is enabled
function unipixel_enqueue_pixel_pinterest()
{
    global $wpdb;
    $platform_id = 2; // Pinterest platform ID
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';

    // Get platform settings
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );
    $platform_settings = $wpdb->get_row($query, ARRAY_A);

    // Check if platform is enabled
    if ($platform_settings && $platform_settings['platform_enabled']) {
        $pinterest_tag_id = esc_js($platform_settings['pixel_id']);
        $pixel_setting = esc_js($platform_settings['pixel_setting']);

        if ($pixel_setting === 'include') {
            // Enqueue the Pinterest pixel script (pixel-pinterest.js)
            wp_enqueue_script('unipixel-pixel-pinterest', plugin_dir_url(__DIR__) . 'js/pixel-pinterest.js', array(), UNIPIXEL_VERSION, false);

            // Build client-side AM data for Pinterest (pintrk('set') supports em)
            $am_client = [];
            $amData = unipixel_get_advanced_matching_data();
            if (!empty($amData['plcehldr_hashedEmail'])) {
                $am_client['em'] = $amData['plcehldr_hashedEmail'];
            }

            // Pass the tag ID and AM data dynamically using wp_localize_script
            wp_localize_script('unipixel-pixel-pinterest', 'pinterestPixelSettings', array(
                'pixel_id' => $pinterest_tag_id,
                'advanced_matching' => $am_client,
            ));
        }
    }
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_pixel_pinterest');


// Enqueues the custom script and localizes data for use in JavaScript
function unipixel_enqueue_scripts_pinterest()
{
    global $wpdb;
    $platform_id = 2; // Pinterest platform ID
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';

    // Get platform settings
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );

    $platformSettings = $wpdb->get_row($query, ARRAY_A);

    $platformEnabled     = ! empty($platformSettings['platform_enabled']);
    $pinterestTagId      = isset($platformSettings['pixel_id']) ? esc_js($platformSettings['pixel_id']) : '';

    if (! $platformEnabled || ! $pinterestTagId) {

        wp_add_inline_script('unipixel-common', "
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Pinterest | Platform tracking disabled — event tracking will not run.');
            }
        ");

        return;
    }

    $events = unipixel_get_events_for_platform($platform_id);

    // Prepare the events array for localization
    $eventsToTrack = array_map(function ($event) {
        return [
            'elementRef'                => $event['element_ref'],
            'trigger'                   => $event['event_trigger'],
            'name'                      => $event['event_name'],
            'send_client'              => (int) ($event['send_client'] ?? 0),
            'send_server'              => (int) ($event['send_server'] ?? 0),
            'send_server_log_response' => (int) ($event['send_server_log_response'] ?? 0)
        ];
    }, $events);

    // Add PageView event — firing conditions determined later
    array_unshift($eventsToTrack, [
        'elementRef' => 'body',
        'trigger'    => 'shown',
        'name'       => 'PageView',
        'send_client'              => (int) ($platformSettings['pageview_send_clientside'] ?? 0),
        'send_server'              => (int) ($platformSettings['pageview_send_serverside'] ?? 0),
        'send_server_log_response' => (int) ($platformSettings['send_server_log_response'] ?? 0)

    ]);

    // Generate the script URL
    $script_url = plugin_dir_url(__DIR__) . 'js/clientfirst-watch-and-send-pinterest.js';

    wp_enqueue_script(
        'clientfirst-watch-and-send-pinterest',
        $script_url,
        ['unipixel-common', 'unipixel-console-logger'],
        UNIPIXEL_VERSION,
        true
    );

    wp_localize_script('clientfirst-watch-and-send-pinterest', 'UniPixelEventDataPinterest', array(
        'eventsToTrack' => $eventsToTrack,
        'customData' => array(),
        'serverside_global_enabled' => (int) ($platformSettings['serverside_global_enabled'] ?? 0),
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('unipixel_track_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_scripts_pinterest', 20);
