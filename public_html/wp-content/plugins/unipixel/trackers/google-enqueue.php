<?php

//File: public_html\wp-content\plugins\unipixel\trackers\google-enqueue.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueue Google Analytics or GTM script
function unipixel_enqueue_pixel_google()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE platform_name = %s AND platform_enabled = %d",
        $table_name,
        'Google',
        1
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $google = $wpdb->get_row($query);

    if (!$google) {
        return;
    }

    $pixel_id = esc_js($google->pixel_id);
    $additional_id = esc_js($google->additional_id);
    $pixel_setting = esc_js($google->pixel_setting);

    // Output Google Analytics gtag.js script based on the platform setting
    if ($pixel_setting === 'include') {

        // 1) Load the real gtag.js library
        wp_enqueue_script(
            'unipixel-google-gtag-lib',
            "https://www.googletagmanager.com/gtag/js?id={$pixel_id}",
            [], // no dependencies
            null, // let browser cache
            false // in head
        );

        // add async attribute like the official GA snippet
        wp_script_add_data('unipixel-google-gtag-lib', 'async', true);

        wp_enqueue_script(
            'unipixel-google-gtag',
            plugin_dir_url(__DIR__) . 'js/pixel-google-gtag.js',
            ['unipixel-google-gtag-lib'], // wait for the library, 
            UNIPIXEL_VERSION,
            false
        );

        // Localize the pixel ID for gtag.js
        wp_localize_script('unipixel-google-gtag', 'googleGtagSettings', array(
            'pixel_id' => $pixel_id,
        ));
    } elseif ($pixel_setting === 'gtm' && $additional_id) {
        wp_enqueue_script('unipixel-google-gtm', plugin_dir_url(__DIR__) . 'js/pixel-google-gtm.js', array(), UNIPIXEL_VERSION, false);

        // Localize the additional ID for GTM
        wp_localize_script('unipixel-google-gtm', 'googleGtmSettings', array(
            'additional_id' => $additional_id,
        ));
    }
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_pixel_google');

// Add noscript tag for GTM
function unipixel_add_gtm_noscript()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE platform_name = %s",
        $table_name,
        'Google'
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $platformSettings = $wpdb->get_row($query);

    if (!$platformSettings || !$platformSettings->platform_enabled || $platformSettings->pixel_setting === 'already_included') {
        return;
    }

    //assumed if gtm, then no script already included too
    if (1 === 2) {

        if ($platformSettings->pixel_setting === 'gtm' && $platformSettings->additional_id) {
?>
            <!-- UniPixel - Google Tag Manager (noscript) -->
            <noscript>
                <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($platformSettings->additional_id); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
            </noscript>
            <!-- End UniPixel - Google Tag Manager (noscript) -->
<?php
        }
    }
}
add_action('wp_body_open', 'unipixel_add_gtm_noscript');

// Enqueue custom script and localize data for use in JavaScript
function unipixel_enqueue_scripts_google()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE platform_name = %s",
        $table_name,
        'Google'
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $platformSettings = $wpdb->get_row($query, ARRAY_A);


    if (! $platformSettings || ! $platformSettings['platform_enabled']) {
        // Inject failure into your custom console
        wp_add_inline_script('unipixel-common', "
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Google | Platform tracking disabled — event tracking will not run.');
            }
        ");

        return;
    }



    if ($platformSettings && $platformSettings['platform_enabled']) {


        // (A) Google helper: must load before the tracker
        wp_register_script(
            'clientfirst-watch-and-send-google-helper',
            plugin_dir_url(__DIR__) . 'js/clientfirst-watch-and-send-google-helper.js',
            [],
            UNIPIXEL_VERSION,
            true // footer
        );
        wp_enqueue_script('clientfirst-watch-and-send-google-helper');


        // Get events for the platform
        $events = unipixel_get_events_for_platform($platformSettings['id']);

        // Prepare the events array for localization
        $eventsToTrack = array_map(function ($event) {
            return [
                'elementRef' => $event['element_ref'],
                'trigger' => $event['event_trigger'],
                'name' => $event['event_name'],
                'send_client'              => (int) ($event['send_client'] ?? 0),
                'send_server'              => (int) ($event['send_server'] ?? 0),
                'send_server_log_response' => (int) ($event['send_server_log_response'] ?? 0)
            ];
        }, $events);

        //Add PageView event //firing conditions determined later
        array_unshift($eventsToTrack, [
            'elementRef'                => 'body',
            'trigger'                   => 'shown',
            'name'                      => 'page_view',
            'send_client'              => (int) ($platformSettings['pageview_send_clientside'] ?? 0),
            'send_server'              => (int) ($platformSettings['pageview_send_serverside'] ?? 0),
            'send_server_log_response' => (int) ($platformSettings['send_server_log_response'] ?? 0)

        ]);

        // Generate the script URL
        $script_url = plugin_dir_url(__DIR__) . 'js/clientfirst-watch-and-send-google.js';


        wp_enqueue_script(
            'clientfirst-watch-and-send-google',
            $script_url,
            ['unipixel-common', 'clientfirst-watch-and-send-google-helper'], // depend on helper so load order is guaranteed
            UNIPIXEL_VERSION,
            true
        );


        wp_localize_script('clientfirst-watch-and-send-google', 'UniPixelEventDataGoogle', array(
            'eventsToTrack' => $eventsToTrack,
            'orderData' => array(
                'currency' => '', // Placeholder
                'value' => ''     // Placeholder
            ),
            'pixel_setting' => $platformSettings['pixel_setting'],
            'serverside_global_enabled' => (int) ($platformSettings['serverside_global_enabled'] ?? 0),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('unipixel_track_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_scripts_google');
