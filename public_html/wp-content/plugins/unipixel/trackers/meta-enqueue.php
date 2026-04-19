<?php

//File: public_html\wp-content\plugins\unipixel\trackers\meta-enqueue.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueues the Facebook Pixel script if the platform is enabled
function unipixel_enqueue_pixel_meta()
{
    global $wpdb;
    $platform_id = 1; // Meta platform ID
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
        $meta_pixel_id = esc_js($platform_settings['pixel_id']);
        $pixel_setting = esc_js($platform_settings['pixel_setting']);

        if ($pixel_setting === 'include') {
            // Enqueue the Meta pixel script (pixel-meta.js)
            wp_enqueue_script('unipixel-pixel-meta', plugin_dir_url(__DIR__) . 'js/pixel-meta.js', array(), UNIPIXEL_VERSION, false);

            // Build client-side AM data for Meta (fbq('init') accepts all PII fields)
            $am_client = [];
            $amData = unipixel_get_advanced_matching_data();
            $meta_client_am_map = [
                'plcehldr_hashedEmail'   => 'em',
                'plcehldr_hashedPhone'   => 'ph',
                'plcehldr_hashedFn'      => 'fn',
                'plcehldr_hashedLn'      => 'ln',
                'plcehldr_hashedCt'      => 'ct',
                'plcehldr_hashedSt'      => 'st',
                'plcehldr_hashedZp'      => 'zp',
                'plcehldr_hashedCountry' => 'country',
            ];
            foreach ($meta_client_am_map as $placeholder => $apiKey) {
                if (!empty($amData[$placeholder])) {
                    $am_client[$apiKey] = $amData[$placeholder];
                }
            }

            // Pass the pixel ID and AM data dynamically using wp_localize_script
            wp_localize_script('unipixel-pixel-meta', 'metaPixelSettings', array(
                'pixel_id' => $meta_pixel_id,
                'advanced_matching' => $am_client,
            ));
        }
    }
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_pixel_meta');


function unipixel_add_facebook_noscript()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE platform_name = %s",
        $table_name,
        'Meta'
    );
    $platform_settings = $wpdb->get_row($query);

    if (!$platform_settings || !$platform_settings->platform_enabled || $platform_settings->pixel_setting === 'already_included') {
        return;
    }

?>
    <!-- UniPixel - Facebook Pixel (noscript) -->
    <noscript>
        <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr($platform_settings->pixel_id); ?>&ev=PageView&noscript=1" />
    </noscript>
    <!-- End UniPixel - Facebook Pixel (noscript) -->
<?php
}

// Hook to add noscript tag at the beginning of the body
add_action('wp_body_open', 'unipixel_add_facebook_noscript');


// Enqueues the custom script and localizes data for use in JavaScript
function unipixel_enqueue_scripts_meta()
{
    global $wpdb;
    $platform_id = 1; // Assuming platform_id for Meta is 1, adjust as necessary
    $table_name = $wpdb->prefix . 'unipixel_platform_settings';

    // Get platform settings
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );

    $platformSettings = $wpdb->get_row($query, ARRAY_A);

    $platformEnabled            = ! empty($platformSettings['platform_enabled']);
    $metaPixelId                = isset($platformSettings['pixel_id']) ? esc_js($platformSettings['pixel_id']) : '';

    if (! $platformEnabled || ! $metaPixelId) {

        wp_add_inline_script('unipixel-common', "
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Meta | Platform tracking disabled — event tracking will not run.');
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

    //Add PageView event //firing conditions determined later
    array_unshift($eventsToTrack, [
        'elementRef' => 'body',
        'trigger'    => 'shown',
        'name'       => 'PageView',
        'send_client'              => (int) ($platformSettings['pageview_send_clientside'] ?? 0),
        'send_server'              => (int) ($platformSettings['pageview_send_serverside'] ?? 0),
        'send_server_log_response' => (int) ($platformSettings['send_server_log_response'] ?? 0)

    ]);

    // Generate the script URL
    $script_url = plugin_dir_url(__DIR__) . 'js/clientfirst-watch-and-send-meta.js';

    wp_enqueue_script(
        'clientfirst-watch-and-send-meta',
        $script_url,
        ['unipixel-common', 'unipixel-console-logger'],   // <-- ensure logger + UniPixelAjax are defined first
        UNIPIXEL_VERSION,
        true
    );

    wp_localize_script('clientfirst-watch-and-send-meta', 'UniPixelEventDataMeta', array(
        'eventsToTrack' => $eventsToTrack,
        'customData' => array(),
        'serverside_global_enabled' => (int) ($platformSettings['serverside_global_enabled'] ?? 0),
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('unipixel_track_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_scripts_meta', 20);
