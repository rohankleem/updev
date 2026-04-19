<?php

/**
 * Handler for updating General (logging & debug) settings.
 */
add_action('wp_ajax_unipixel_update_general_settings', 'unipixel_handle_general_update');

function unipixel_handle_general_update()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    // --- Handle ADVANCED MATCHING ---
    $new_am_enabled = isset($_POST['advanced_matching_enabled']) ? true : false;
    $current_am_enabled = get_option('unipixel_advanced_matching_enabled', true);
    $am_updated = false;
    if ($new_am_enabled !== $current_am_enabled) {
        update_option('unipixel_advanced_matching_enabled', $new_am_enabled);
        $am_updated = true;
    }

    // --- Handle LOGGING OPTIONS ---
    $admin_logging         = isset($_POST['enableLogging_Admin'])        ? intval($_POST['enableLogging_Admin'])        : 0;
    $initiate_logging      = isset($_POST['enableLogging_InitiateEvents']) ? intval($_POST['enableLogging_InitiateEvents']) : 0;
    $send_logging          = isset($_POST['enableLogging_SendEvents'])    ? intval($_POST['enableLogging_SendEvents'])    : 0;
    $google_debug_view_clientside = isset($_POST['enableGoogleDebugViewClientSide']) ? intval($_POST['enableGoogleDebugViewClientSide']) : 0;
    $google_debug_view_serverside = isset($_POST['enableGoogleDebugViewServerSide']) ? intval($_POST['enableGoogleDebugViewServerSide']) : 0;

    $newOptions = array(
        'enableLogging_Admin'               => (bool) $admin_logging,
        'enableLogging_InitiateEvents'      => (bool) $initiate_logging,
        'enableLogging_SendEvents'          => (bool) $send_logging,
        'enableGoogleDebugViewClientSide'   => (bool) $google_debug_view_clientside,
        'enableGoogleDebugViewServerSide'   => (bool) $google_debug_view_serverside,
    );

    $defaults = array(
        'enableLogging_Admin'               => false,
        'enableLogging_InitiateEvents'      => false,
        'enableLogging_SendEvents'          => false,
        'enableGoogleDebugViewClientSide'   => false,
        'enableGoogleDebugViewServerSide'   => false,
    );

    $current_options = get_option('unipixel_logging_options', false);
    if ($current_options === false) {
        $current_options = $defaults;
    }

    // --- Handle DBSTORE SETTINGS ---
    $default_dbstore = unipixel_get_dbstore_events_schema();

    $new_dbstore = [
        'dbstore_pageview_events' => isset($_POST['dbstore_pageview_events']) ? true : false,
        'dbstore_custom_events'   => isset($_POST['dbstore_custom_events']) ? true : false,
        'dbstore_woocommerce_events' => []
    ];

    foreach ($default_dbstore['dbstore_woocommerce_events'] as $eventKey => $defaultValue) {
        $new_dbstore['dbstore_woocommerce_events'][$eventKey] =
            isset($_POST['dbstore_woocommerce_events'][$eventKey]) ? true : false;
    }

    $existing_dbstore = unipixel_get_dbstore_event_settings();

    // --- Save logic BEFORE early return ---
    $dbstore_updated = false;
    if ($new_dbstore !== $existing_dbstore) {
        update_option('unipixel_dbstore_settings', $new_dbstore);
        $dbstore_updated = true;
    }

    // --- NOW Check if both are unchanged ---
    if (
        $current_options['enableLogging_Admin']                    === $newOptions['enableLogging_Admin'] &&
        $current_options['enableLogging_InitiateEvents']           === $newOptions['enableLogging_InitiateEvents'] &&
        $current_options['enableLogging_SendEvents']               === $newOptions['enableLogging_SendEvents'] &&
        $current_options['enableGoogleDebugViewClientSide']        === $newOptions['enableGoogleDebugViewClientSide'] &&
        $current_options['enableGoogleDebugViewServerSide']        === $newOptions['enableGoogleDebugViewServerSide'] &&
        !$dbstore_updated &&
        !$am_updated
    ) {
        wp_send_json_success(array('message' => 'No changes entered'));
        return;
    }

    // --- Save logging options ---
    $updated = update_option('unipixel_logging_options', $newOptions);

    // Log metric
    unipixel_metric_log(
        "General Settings Updated",
        "Logging & DebugView Settings",
        array(
            'admin_logging'                 => $newOptions['enableLogging_Admin'],
            'initiate_logging'              => $newOptions['enableLogging_InitiateEvents'],
            'send_logging'                  => $newOptions['enableLogging_SendEvents'],
            'google_debug_view_clientside'  => $newOptions['enableGoogleDebugViewClientSide'],
            'google_debug_view_serverside'  => $newOptions['enableGoogleDebugViewServerSide'],
            'advanced_matching_enabled'     => $new_am_enabled,
        )
    );

    // Final response
    wp_send_json_success(array('message' => 'Settings updated successfully'));
}
