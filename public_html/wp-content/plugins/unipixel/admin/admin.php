<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


require_once 'handlers/handler-general-settings.php';
require_once 'handlers/handler-platform-settings.php';
require_once 'handlers/handler-event-settings.php'; 
require_once 'handlers/handler-event-woocomm-settings.php'; 
require_once 'handlers/handler-consent-settings.php'; 
require_once 'handlers/handler-feedback.php';
require_once 'inc/feedback.php';
require_once 'admin-wpmenu.php';
require_once 'page-home.php';
require_once 'page-console-logger.php';
require_once 'page-general-settings.php';
require_once 'page-consent-settings.php';
require_once 'page-meta-setup.php';
require_once 'page-meta-events.php';
require_once 'page-pinterest-setup.php';
require_once 'page-pinterest-events.php';
require_once 'page-tiktok-setup.php';
require_once 'page-tiktok-events.php';
require_once 'page-google-setup.php';
require_once 'page-google-events.php';
require_once 'page-microsoft-setup.php';
require_once 'page-microsoft-events.php';
require_once 'page-event-logs.php';


function unipixel_suppress_admin_notices() {
    // Get the current screen
    $screen = get_current_screen();
    
    // List of your custom admin pages
    $custom_pages = array(
        'toplevel_page_unipixel',
        'unipixel_page_setup_meta',
        'unipixel_page_setup_tiktok',
        'unipixel_page_setup_google',
        'unipixel_page_setup_microsoft'
    );

    if (in_array($screen->id, $custom_pages)) {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
add_action('current_screen', 'unipixel_suppress_admin_notices');



function unipixel_admin_enqueue($hook) {
  
    if ($hook == 'toplevel_page_unipixel' || strpos($hook, 'unipixel_page_') !== false) {
        // Scripts and styles are only enqueued if we are on one of the UniPixel pages

        wp_enqueue_style('unipixel-fontawesome',plugin_dir_url(__FILE__) . 'vendor/fontawesome/css/all.min.css', array(),'6.7.2', 'all');
        wp_enqueue_style('bootstrap-css', plugins_url('admin/css/bootstrap.min.css', __DIR__), array(), UNIPIXEL_VERSION);
        wp_enqueue_style('unipixel_admin_css', plugins_url('admin/css/admin.css', __DIR__), array(), UNIPIXEL_VERSION);

        wp_enqueue_script('bootstrap_bundle_js', plugins_url('admin/js/bootstrap.bundle.min.js', __DIR__), array('jquery'), UNIPIXEL_VERSION, true);
        wp_enqueue_script('unipixel_popover_js', plugins_url('admin/js/popover-init.js', __DIR__), array('jquery', 'bootstrap_bundle_js'), UNIPIXEL_VERSION, true);

        wp_enqueue_script('unipixel-admin-common-js', plugins_url('admin/js/admin-common.js', __DIR__), array('jquery'), UNIPIXEL_VERSION, true);
        wp_enqueue_script('unipixel-load-platform-settings-js', plugins_url('admin/js/form-load-platform-settings.js', __DIR__), array('jquery'), UNIPIXEL_VERSION, true);

        // Additional script for AJAX operations
        wp_enqueue_script('unipixel-ajax-consent-settings', plugins_url('admin/js/ajax-consent-settings.js', __DIR__), array('jquery'), UNIPIXEL_VERSION, true);
        wp_enqueue_script('unipixel-ajax-platform-settings', plugins_url('admin/js/ajax-platform-settings.js', __DIR__), array('jquery'), UNIPIXEL_VERSION, true);
        wp_enqueue_script('unipixel-ajax-event-settings', plugins_url('admin/js/ajax-event-settings.js', __DIR__), array('jquery'), UNIPIXEL_VERSION, true);

        wp_enqueue_script('unipixel-console-logger', plugins_url('/js/unipixel-console-logger.js', __DIR__), array('jquery'),UNIPIXEL_VERSION, true);
        wp_enqueue_script('unipixel-console-log-admin', plugins_url('admin/js/unipixel-console-logger-admin.js', __DIR__), array('jquery'),  UNIPIXEL_VERSION, true);

        wp_enqueue_script('unipixel-apply-recommended', plugins_url('admin/js/unipixel-apply-recommended.js', __DIR__),array('jquery', 'bootstrap_bundle_js'), UNIPIXEL_VERSION, true);

        wp_localize_script('unipixel-ajax-platform-settings', 'unipixel_ajax_obj', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('unipixel_ajax_nonce')
        ));

    }
}
add_action('admin_enqueue_scripts', 'unipixel_admin_enqueue');



/**
 * Render feedback modal in admin footer on all UniPixel pages.
 */
function unipixel_admin_footer_feedback_modal() {
    $screen = get_current_screen();
    if (!$screen) return;
    if ($screen->id !== 'toplevel_page_unipixel' && strpos($screen->id, 'unipixel_page_') === false) return;
    unipixel_render_feedback_modal();
}
add_action('admin_footer', 'unipixel_admin_footer_feedback_modal');


function unipixel_enqueue_admin_feedback_scripts() {
	wp_enqueue_script('unipixel-admin-feedback', plugin_dir_url(__FILE__) . 'js/admin-feedback.js', array('jquery'), UNIPIXEL_VERSION, true);
	wp_localize_script('unipixel-admin-feedback', 'UniPixelFeedbackAjax', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('unipixel_feedback_nonce'),
	));
}
add_action('admin_enqueue_scripts', 'unipixel_enqueue_admin_feedback_scripts');


function unipixel_enqueue_admin_general_settings_scripts() {
    wp_enqueue_script('unipixel-admin-general-settings', plugin_dir_url(__FILE__) . 'js/ajax-general-settings.js', array('jquery'), UNIPIXEL_VERSION, true);
    wp_localize_script(
        'unipixel-admin-general-settings', 'UniPixelGeneralSettingsAjax',  array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('unipixel_ajax_nonce'),
        )
    );
}
add_action('admin_enqueue_scripts', 'unipixel_enqueue_admin_general_settings_scripts');


// Optional run update schema function
//add_action('admin_init', 'unipixel_update_schema');
