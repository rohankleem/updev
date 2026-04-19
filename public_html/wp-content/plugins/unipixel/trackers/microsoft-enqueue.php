<?php

//File: public_html\wp-content\plugins\unipixel\trackers\microsoft-enqueue.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueues the Microsoft Pixel script if the platform is enabled
function unipixel_enqueue_pixel_microsoft() {
    global $wpdb;
    $platform_id = 5; // Microsoft platform ID
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
        $microsoft_pixel_id = $platform_settings['pixel_id'];

        // Enqueue the Microsoft pixel script (pixel-microsoft.js)
        wp_enqueue_script('unipixel-pixel-microsoft', plugin_dir_url(__DIR__) . 'js/pixel-microsoft.js', array(), UNIPIXEL_VERSION, false);

        // Pass the pixel ID dynamically using wp_localize_script
        wp_localize_script('unipixel-pixel-microsoft', 'microsoftPixelSettings', array(
            'pixel_id' => esc_js($microsoft_pixel_id),
        ));
    }
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_pixel_microsoft');

function unipixel_add_microsoft_noscript() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE platform_name = %s AND platform_enabled = %d",
        $table_name,
        'Microsoft',
        1
    );
    $microsoft = $wpdb->get_row($query);

    if (!$microsoft) {
        return;
    }

    ?>
    <!-- UniPixel - Microsoft Pixel (noscript) -->
    <noscript>
        <img height="1" width="1" style="display:none" src="https://bat.bing.com/action/0?ti=<?php echo esc_attr($microsoft->pixel_id); ?>&amp;Ver=2" />
    </noscript>
    <!-- End UniPixel - Microsoft Pixel (noscript) -->
<?php
}

// Hook to add noscript tag at the beginning of the body
add_action('wp_body_open', 'unipixel_add_microsoft_noscript');

// Enqueues the custom script and localizes data for use in JavaScript
function unipixel_enqueue_scripts_microsoft() {
    global $wpdb;
    $platform_id = 5; // Microsoft platform ID
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';

    // Get platform settings
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );
    $platform_settings = $wpdb->get_row($query, ARRAY_A);

    // Only proceed if enabled + tag present
    $platform_enabled = !empty($platform_settings['platform_enabled']);
    $microsoft_tag_id = isset($platform_settings['pixel_id']) ? esc_js($platform_settings['pixel_id']) : '';

    if (!$platform_enabled || !$microsoft_tag_id) {
        // Console hint (matches Meta/Google pattern)
        wp_add_inline_script('unipixel-common', "
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Microsoft | Platform tracking disabled — event tracking will not run.');
            }
        ");
        return;
    }

    // Get custom events for Microsoft (client-side only)
    $events = unipixel_get_events_for_platform($platform_id);

    // Prepare events for localization (no event_id here; the JS generates it per trigger)
    $eventsToTrack = array_map(function ($event) {
        return [
            'elementRef'                => $event['element_ref'],
            'trigger'                   => $event['event_trigger'],
            'name'                      => $event['event_name'],
            'send_client'              => (int) ($event['send_client'] ?? 1),
            'send_server'              => (int) ($event['send_server'] ?? 0),
            'send_server_log_response' => (int) ($event['send_server_log_response'] ?? 0)
        ];
    }, $events);

    // Add PageView event — firing conditions determined later
    array_unshift($eventsToTrack, [
        'elementRef' => 'body',
        'trigger'    => 'shown',
        'name'       => 'PageView',
        'send_client'              => (int) ($platform_settings['pageview_send_clientside'] ?? 0),
        'send_server'              => (int) ($platform_settings['pageview_send_serverside'] ?? 0),
        'send_server_log_response' => (int) ($platform_settings['send_server_log_response'] ?? 0)
    ]);

    // Enqueue the unified client-first watcher (renamed file)
    $script_url = plugin_dir_url(__DIR__) . 'js/clientfirst-watch-and-send-microsoft.js';

    wp_enqueue_script(
        'clientfirst-watch-and-send-microsoft',
        $script_url,
        ['unipixel-common', 'unipixel-console-logger'], // same deps as Meta/Google
        UNIPIXEL_VERSION,
        true
    );

    wp_localize_script('clientfirst-watch-and-send-microsoft', 'UniPixelEventDataMicrosoft', [
        'eventsToTrack'            => $eventsToTrack,
        'serverside_global_enabled' => (int) ($platform_settings['serverside_global_enabled'] ?? 0),
        'ajaxurl'                  => admin_url('admin-ajax.php'),
        'nonce'                    => wp_create_nonce('unipixel_track_nonce')
    ]);
}

add_action('wp_enqueue_scripts', 'unipixel_enqueue_scripts_microsoft', 20);

?>
