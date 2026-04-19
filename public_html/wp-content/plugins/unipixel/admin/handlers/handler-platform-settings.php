<?php
add_action('wp_ajax_unipixel_update_platform', 'unipixel_handle_platform_update');

// Handler for updating platform settings
function unipixel_handle_platform_update() {
    global $wpdb;

    // Verify nonce
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    // Check required parameters with validation
    if (
        !isset($_POST['platform_id'], $_POST['pixel_id'], $_POST['access_token'], $_POST['platform_enabled'])
    ) {
        wp_send_json_error(array('message' => 'Missing parameters'));
        return;
    }

    // Optional parameters with sanitization
    $additional_id = isset($_POST['additional_id']) 
        ? sanitize_text_field(wp_unslash($_POST['additional_id'])) 
        : null;

    $pixel_setting = !empty($_POST['pixel_setting']) 
        ? sanitize_text_field(wp_unslash($_POST['pixel_setting'])) 
        : 'include';

    // Sanitize and validate input data
    $platform_id = intval($_POST['platform_id']);
    $pixel_id = sanitize_text_field(wp_unslash($_POST['pixel_id']));
    $access_token = sanitize_text_field(wp_unslash($_POST['access_token']));
    $platform_enabled = intval($_POST['platform_enabled']);
    $pageview_send_serverside = intval($_POST['pageview_send_serverside']);
    $pageview_send_clientside = isset($_POST['pageview_send_clientside']) ? intval($_POST['pageview_send_clientside']) : 1;
    $serverside_global_enabled = isset($_POST['serverside_global_enabled']) ? intval($_POST['serverside_global_enabled']) : 0;


    $table_name = $wpdb->prefix . 'unipixel_platform_settings';

    // Fetch the current values from the database
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT platform_name, pixel_id, access_token, platform_enabled, additional_id, pixel_setting, pageview_send_serverside, serverside_global_enabled FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $current_data = $wpdb->get_row($query, ARRAY_A);

    // Check if the data has changed
    if (
        $current_data['pixel_id'] === $pixel_id &&
        $current_data['access_token'] === $access_token &&
        $current_data['platform_enabled'] === $platform_enabled &&
        ($current_data['additional_id'] === $additional_id || $additional_id === null) &&
        ($current_data['pixel_setting'] === $pixel_setting || $pixel_setting === null) &&
        ($current_data['pageview_send_serverside'] === $pageview_send_serverside || $pageview_send_serverside === null) &&
        (isset($current_data['serverside_global_enabled']) && (int) $current_data['serverside_global_enabled'] === $serverside_global_enabled)
    ) {
        wp_send_json_success(array('message' => 'No changes entered'));
        return;
    }

    // Prepare data for update
    $update_data = array(
        'pixel_id' => $pixel_id,
        'access_token' => $access_token,
        'platform_enabled' => $platform_enabled,
        'pageview_send_serverside' => $pageview_send_serverside,
        'pageview_send_clientside' => $pageview_send_clientside,
        'serverside_global_enabled' => $serverside_global_enabled,
    );

    $format = array('%s','%s','%d','%d','%d','%d');

    if ($additional_id !== null) {
        $update_data['additional_id'] = $additional_id;
        $format[] = '%s';
    }

    if ($pixel_setting !== null) {
        $update_data['pixel_setting'] = $pixel_setting;
        $format[] = '%s';
    }

    // Update the database
    $updated = $wpdb->update(
        $table_name,
        $update_data,
        array('id' => $platform_id),
        $format,
        array('%d')
    );


    $action = "Platform Settings";
    $platformName = $current_data['platform_name'];
    $platformEnabledStr = "Disabled";
    if ($platform_enabled){
        $platformEnabledStr = "Enabled";
    }

    $platformPageViewServerSide = "false";
    if ($pageview_send_serverside){
        $platformPageViewServerSide = "true";
    }
    unipixel_metric_log(
        $action,
        $platformName,
        array(
            'PlatformId'              => $platform_id,
            'PlatformEnable'          => $platformEnabledStr,
            'PageViewServerSide'      => $platformPageViewServerSide,
            'ServerSideGlobalEnabled' => $serverside_global_enabled ? 'true' : 'false',
        )
    );

    if ($updated !== false) {
        wp_send_json_success(array('message' => 'Settings updated successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update settings'));
    }


    
}
?>
