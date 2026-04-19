<?php
add_action('wp_ajax_unipixel_get_events', 'unipixel_handle_get_events');
add_action('wp_ajax_unipixel_update_event', 'unipixel_handle_update_event');
add_action('wp_ajax_unipixel_add_event', 'unipixel_handle_add_event');
add_action('wp_ajax_unipixel_delete_event', 'unipixel_handle_delete_event');
add_action('wp_ajax_unipixel_update_platform_pageview', 'unipixel_update_platform_pageview');

function unipixel_handle_get_events()
{
    global $wpdb;

    // Verify nonce
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    $platform_id = intval($_POST['platform_id']);
    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE platform_id = %d",
        $events_table,
        $platform_id
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $events = $wpdb->get_results($query, ARRAY_A);

    wp_send_json_success(array('events' => $events));
}

function unipixel_handle_update_event()
{
    global $wpdb;

    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    $event_id = intval($_POST['id']);
    $element_ref = sanitize_text_field(wp_unslash($_POST['element_ref']));
    $event_trigger = sanitize_text_field(wp_unslash($_POST['event_trigger']));
    $event_name = sanitize_text_field(wp_unslash($_POST['event_name']));
    $event_description = sanitize_textarea_field(wp_unslash($_POST['event_description']));
    $events_table = $wpdb->prefix . 'unipixel_events_settings';
    $send_client = array_key_exists('send_client', $_POST) ? absint(wp_unslash($_POST['send_client'])) : 0;
    $send_server = array_key_exists('send_server', $_POST) ? absint(wp_unslash($_POST['send_server'])) : 0;
    $send_server_log_response = array_key_exists('send_server_log_response', $_POST) ? absint(wp_unslash($_POST['send_server_log_response'])) : 0;


    $updated = $wpdb->update(
        $events_table,
        array(
            'element_ref' => $element_ref,
            'event_trigger' => $event_trigger,
            'event_name' => $event_name,
            'event_description' => $event_description,
            'send_client' => $send_client,
            'send_server' => $send_server,
            'send_server_log_response' => $send_server_log_response
        ),
        array('id' => $event_id),
        array('%s', '%s', '%s', '%s', '%d', '%d', '%d'),
        array('%d')
    );

    unipixel_metric_log(
        "Event update",
        "N/A",
        array(
            'element_ref' => $element_ref,
            'event_trigger' => $event_trigger,
            'event_name' => $event_name,
            'event_description' => $event_description
        )
    );


    if ($updated !== false) {
        wp_send_json_success(array('message' => 'Events updated successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update event'));
    }
}

function unipixel_handle_add_event()
{
    global $wpdb;

    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    $platform_id = intval($_POST['platform_id']);
    $element_ref = sanitize_text_field(wp_unslash($_POST['element_ref']));
    $event_trigger = sanitize_text_field(wp_unslash($_POST['event_trigger']));
    $event_name = sanitize_text_field(wp_unslash($_POST['event_name']));
    $event_description = sanitize_textarea_field(wp_unslash($_POST['event_description']));
    $send_client = array_key_exists('send_client', $_POST) ? absint(wp_unslash($_POST['send_client'])) : 0;
    $send_server = array_key_exists('send_server', $_POST) ? absint(wp_unslash($_POST['send_server'])) : 0;
    $send_server_log_response = array_key_exists('send_server_log_response', $_POST) ? absint(wp_unslash($_POST['send_server_log_response'])) : 0;

    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    // Check if the event already exists
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $existing_event = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM %i WHERE platform_id = %d AND element_ref = %s AND event_trigger = %s AND event_name = %s",
        $events_table,
        $platform_id,
        $element_ref,
        $event_trigger,
        $event_name
    ));

    if ($existing_event) {
        // Return the existing ID so UI can attach it
        wp_send_json_success(array(
            'message' => 'Updated.',
            'id'      => (int) $existing_event
        ));
    }

    // Insert the event if it doesn't exist
    $inserted = $wpdb->insert(
        $events_table,
        array(
            'platform_id' => $platform_id,
            'element_ref' => $element_ref,
            'event_trigger' => $event_trigger,
            'event_name' => $event_name,
            'event_description' => $event_description,
            'send_client' => $send_client,
            'send_server' => $send_server,
            'send_server_log_response' => $send_server_log_response
        ),
        array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d')
    );

    unipixel_metric_log(
        "Event add",
        unipixel_get_platform_name($platform_id),
        array(
            'element_ref' => $element_ref,
            'event_trigger' => $event_trigger,
            'event_name' => $event_name,
            'event_description' => $event_description
        )
    );

    if ($inserted) {
        $new_id = (int) $wpdb->insert_id;
        wp_send_json_success(array(
            'message' => 'Event added successfully.',
            'id'      => $new_id
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to add event. Please try again.'));
    }
}



function unipixel_handle_delete_event()
{
    global $wpdb;

    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    $event_id = intval($_POST['id']);
    $events_table = $wpdb->prefix . 'unipixel_events_settings';
    $deleted = $wpdb->delete($events_table, array('id' => $event_id), array('%d'));

    unipixel_metric_log(
        "Event delete",
        "N/A",
        array(
            'event_id' => $event_id
        )
    );

    if ($deleted !== false) {
        wp_send_json_success(array('message' => 'Event deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to delete event'));
    }
}


/**
 * AJAX: Update Platform PageView toggles (client/server)
 */
function unipixel_update_platform_pageview()
{
    // Capability check (adjust capability if your plugin uses a different one)
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Unauthorized.', 'unipixel')], 403);
    }

    // AJAX nonce check (matches the string used when localizing the nonce)
    // e.g. wp_localize_script(..., 'unipixel_ajax_obj', ['nonce' => wp_create_nonce('unipixel_ajax_nonce')]);
    if (! check_ajax_referer('unipixel_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => __('Invalid nonce.', 'unipixel')], 400);
    }

    // Require POST
    if ('POST' !== $_SERVER['REQUEST_METHOD']) {
        wp_send_json_error(['message' => __('Invalid method.', 'unipixel')], 405);
    }

    // Sanitize input
    $platform_id = isset($_POST['platform_id']) ? absint(wp_unslash($_POST['platform_id'])) : 0;

    // Checkboxes: absent means 0. If present, coerce to 0/1 safely.
    $send_client = array_key_exists('pageview_send_clientside', $_POST) ? absint(wp_unslash($_POST['pageview_send_clientside'])) : 0;
    $send_server = array_key_exists('pageview_send_serverside', $_POST) ? absint(wp_unslash($_POST['pageview_send_serverside'])) : 0;
    $send_log_response = array_key_exists('send_server_log_response', $_POST) ? absint(wp_unslash($_POST['send_server_log_response'])) : 0;

    // Constrain to 0/1 in case something weird got posted
    $send_client = $send_client ? 1 : 0;
    $send_server = $send_server ? 1 : 0;
    $send_log_response = $send_log_response ? 1 : 0;

    if ($platform_id <= 0) {
        wp_send_json_error(['message' => __('Invalid platform id.', 'unipixel')], 400);
    }

    global $wpdb;
    $platform_table = $wpdb->prefix . 'unipixel_platform_settings';

    // Ensure the platform row exists before updating
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $exists = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(1) FROM %i WHERE id = %d",
        $platform_table,
        $platform_id
    ));

    if ($exists !== 1) {
        wp_send_json_error(['message' => __('Platform not found.', 'unipixel')], 404);
    }

    // Update only the two columns; treat 0 rows (no change) as success
    $updated = $wpdb->update(
        $platform_table,
        [
            'pageview_send_clientside'   => $send_client,
            'pageview_send_serverside'   => $send_server,
            'send_server_log_response'   => $send_log_response,
        ],
        ['id' => $platform_id],
        ['%d', '%d', '%d'],
        ['%d']
    );

    if ($updated === false) {
        // DB error
        wp_send_json_error(['message' => __('Database update failed.', 'unipixel')], 500);
    }

    // Success (even if nothing actually changed)
    wp_send_json_success([
        'message' => ($updated > 0)
            ? __('PageView settings updated.', 'unipixel')
            : __('No changes to save.', 'unipixel')
    ]);
}
