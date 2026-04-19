<?php

add_action('wp_ajax_unipixel_update_woo_events_batch', 'unipixel_handle_update_woo_events_batch');

function unipixel_handle_update_woo_events_batch()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_woocomm_event_settings';

    // Expect an array of objects => [ {id: X, enabled: Y}, {...} ]
    $eventsData = isset($_POST['eventsData']) ? (array) $_POST['eventsData'] : [];
    if (empty($eventsData)) {
        wp_send_json_error(['message' => 'No event data provided.']);
    }

    foreach ($eventsData as $row) {
        $id = (int) $row['id'];
        $send_client = isset($row['send_client']) ? (int)$row['send_client'] : 0;
        $send_server = isset($row['send_server']) ? (int)$row['send_server'] : 0;
        $enabled     = ($send_client || $send_server) ? 1 : 0;
        $logresponse = isset($row['logresponse']) ? (int) $row['logresponse'] : 0;
        $platformRef = sanitize_text_field($row['event_platform_ref']);

        // Update 'event_enabled' field
        $wpdb->update(
            $table_name,
            [
                'send_client' => $send_client,
                'send_server' => $send_server,
                'event_enabled' => $enabled, // legacy mirror for one release
                'send_server_log_response' => $logresponse
            ],
            ['id' => $id],
            ['%d', '%d', '%d', '%d'],
            ['%d']
        );

        unipixel_metric_log(
            "Woo eComm event update",
            "N/A",
            [
                'id' => $id,
                'enabled' => $enabled,
                'log_response' => $logresponse,
                'event_platform_ref' => $platformRef
            ]
        );
    }

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'DB error: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'WooCommerce events updated successfully']);
}
