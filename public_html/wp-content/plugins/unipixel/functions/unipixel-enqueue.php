<?php

//File: public_html\wp-content\plugins\unipixel\functions\unipixel-enqueue.php

// 5. Enqueue the UniPixelConsoleLogger first (if enabled)
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'unipixel-console-logger',
        plugin_dir_url(__DIR__) . 'js/unipixel-console-logger.js',
        [],
        UNIPIXEL_VERSION,
        true
    );
}, 5);

// 10. Enqueue common data script
function unipixel_enqueue_common_data_script() {
    $script_url = plugin_dir_url(__DIR__) . 'js/unipixel-common.js';

    wp_enqueue_script(
        'unipixel-common',
        $script_url,
        ['jquery', 'unipixel-console-logger'], // jquery needed by all platform tracker scripts; logger must load first
        UNIPIXEL_VERSION,
        true
    );

    // Localize order data (used by other scripts later)
    wp_localize_script('unipixel-common', 'UniPixelOrderData', []);

    // Logging + consent options
    $logging_defaults = [
        'enableLogging_Admin'               => false,
        'enableLogging_InitiateEvents'      => false,
        'enableLogging_SendEvents'          => false,
        'enableGoogleDebugViewClientSide'   => false,
        'enableGoogleDebugViewServerSide'   => false
    ];
    $consent_defaults = [
        'consent_honour'   => 0,
        'consent_ui'       => 0,
        'consent_ui_style' => 1
    ];

    $logging_options = get_option('unipixel_logging_options', $logging_defaults);
    $consent_options = get_option('unipixel_consent_settings', $consent_defaults);
    // Defensive: get_option returns the stored value even if it's not an array
    // (e.g. corrupted to '' or null). Fall back to defaults to keep array_merge safe.
    if (!is_array($logging_options)) { $logging_options = $logging_defaults; }
    if (!is_array($consent_options)) { $consent_options = $consent_defaults; }
    $unipixel_settings = array_merge($logging_defaults, $logging_options, $consent_defaults, $consent_options);

    wp_localize_script('unipixel-common', 'UniPixelAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('unipixel_log_client_event_nonce')
    ]);

    wp_localize_script('unipixel-common', 'UniPixelSettings', $unipixel_settings);
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_common_data_script', 10);

// 20. Enqueue the consent script
function unipixel_enqueue_consent_script() {
    $script_url = plugin_dir_url(__DIR__) . 'js/unipixel-consent.js';

    wp_enqueue_script(
        'unipixel-consent',
        $script_url,
        ['unipixel-common'],
        UNIPIXEL_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'unipixel_enqueue_consent_script', 20);
