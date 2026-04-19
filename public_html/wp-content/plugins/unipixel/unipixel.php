<?php

//File: public_html\wp-content\plugins\unipixel\unipixel.php

/**
 * Plugin Name: UniPixel
 * Plugin URI: https://buildio.dev/unipixel
 * Description: Server-side event tracking for Meta, Pinterest, TikTok, Google and Microsoft. One install to connect your site with event tracking APIs. Includes custom events and consent.
 * Version: 2.6.3
 * Author: Buildio
 * Author URI: https://buildio.dev/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') or die('Direct script access disallowed.');

define('UNIPIXEL_VERSION', '2.6.3');


require_once plugin_dir_path(__FILE__) . 'config/schema.php'; 
require_once plugin_dir_path(__FILE__) . 'functions/unipixel-functions.php';
require_once plugin_dir_path(__FILE__) . 'functions/hooks.php';
require_once plugin_dir_path(__FILE__) . 'functions/send-server-event.php';
require_once plugin_dir_path(__FILE__) . 'functions/send-server-event-handle-result.php';
require_once plugin_dir_path(__FILE__) . 'functions/ajax-handle-log-client-event.php';
require_once plugin_dir_path(__FILE__) . 'functions/consent.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-unipixel-log.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/helpers.php';

require_once plugin_dir_path(__FILE__) . 'config/activation.php';
register_activation_hook(__FILE__, 'unipixel_activate');

require_once plugin_dir_path(__FILE__) . 'functions/unipixel-enqueue.php';

require_once plugin_dir_path(__FILE__) . 'trackers/google-enqueue.php';
require_once plugin_dir_path(__FILE__) . 'trackers/google-ajax-listener-send-server.php';

require_once plugin_dir_path(__FILE__) . 'trackers/meta-enqueue.php';
require_once plugin_dir_path(__FILE__) . 'trackers/meta-ajax-listener-send-server.php';

require_once plugin_dir_path(__FILE__) . 'trackers/tiktok-enqueue.php';
require_once plugin_dir_path(__FILE__) . 'trackers/tiktok-ajax-listener-send-server.php';

require_once plugin_dir_path(__FILE__) . 'trackers/pinterest-enqueue.php';
require_once plugin_dir_path(__FILE__) . 'trackers/pinterest-ajax-listener-send-server.php';

require_once plugin_dir_path(__FILE__) . 'trackers/microsoft-enqueue.php';
require_once plugin_dir_path(__FILE__) . 'trackers/microsoft-handler.php';

require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/localize-and-send-consolelogging.php';

require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/get-common-woo-data-addtocart.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/get-common-woo-data-checkout.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/get-common-woo-data-purchase.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/get-common-woo-data-viewcontent.php';

require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/prepare-common-to-platform-addtocart.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/prepare-common-to-platform-checkout.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/prepare-common-to-platform-purchase.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/prepare-common-to-platform-viewcontent.php';

require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-localize-addtocart.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-localize-checkout.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-localize-purchase.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-localize-viewcontent.php';

require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-send-addtocart.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-send-checkout.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-send-purchase.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/client-side-send-viewcontent.php';

require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/hook-handlers-addtocart.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/hook-handlers-checkout.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/hook-handlers-purchase.php';
require_once plugin_dir_path(__FILE__) . 'woocomm-hook-handling/hook-handlers-viewcontent.php';

require_once plugin_dir_path(__FILE__) . 'admin/admin.php';


function unipixel_activate() {

    unipixel_update_schema();

    update_option('unipixel_version', UNIPIXEL_VERSION);

    unipixel_metric_log(
        'Activate Plugin', 
        'N/A',           
        array(
            'info' => 'Plugin activated',
            'version' => UNIPIXEL_VERSION,
        )
    );
}
register_activation_hook(__FILE__, 'unipixel_activate');


function unipixel_deactivate() {

    unipixel_metric_log(
        'Deactivate Plugin',
        'N/A',
        array(
            'info' => 'Plugin deactivated',
        )
    );
}
register_deactivation_hook(__FILE__, 'unipixel_deactivate');


function unipixel_uninstall() {

    unipixel_metric_log(
        'Uninstall Plugin',
        'N/A',
        array(
            'info' => 'Plugin uninstalled'
        )
    );
}
register_uninstall_hook(__FILE__, 'unipixel_uninstall');



function unipixel_check_version() {
    $current_version = get_option('unipixel_version');
    if ($current_version !== UNIPIXEL_VERSION) {
        unipixel_update_schema();
        update_option('unipixel_version', UNIPIXEL_VERSION);
        unipixel_metric_log('Plugin Update','N/A',
            array(
                'info' => "Auto-updated schema from $current_version to " . UNIPIXEL_VERSION
            )
        );
    }
}
add_action('plugins_loaded', 'unipixel_check_version');



add_action('wp_enqueue_scripts', 'unipixel_enqueue_consent_popup');

function unipixel_enqueue_consent_popup() {

    // Don't load on admin or login pages
    if (is_admin() || in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'], true)) {
        return;
    }

    $settings = get_option('unipixel_consent_settings', []);

    $consentHonour = isset($settings['consent_honour']) ? (int)$settings['consent_honour'] : 0;
    $consentUI     = isset($settings['consent_ui']) ? $settings['consent_ui'] : 'unipixel';

    // Only enqueue if honour consent is ON and vendor is UniPixel
    if ($consentHonour === 1 && $consentUI === 'unipixel') {
        wp_enqueue_script(
            'unipixel-consent-popup',
            plugin_dir_url(__FILE__) . 'js/unipixel-consent-popup.js',
            [],
            UNIPIXEL_VERSION,
            true
        );

        wp_enqueue_style(
            'unipixel-consent-popup',
            plugin_dir_url(__FILE__) . 'css/unipixel-consent-popup.css',
            [],
            UNIPIXEL_VERSION
        );
    }
}
