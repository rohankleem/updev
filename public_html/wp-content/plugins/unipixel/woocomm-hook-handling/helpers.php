<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\helpers.php

function unipixel_is_woo_event_enabled($platform_id, $event_platform_ref)
{
    global $wpdb;


    // Check if the platform is enabled
    $platform_table = $wpdb->prefix . 'unipixel_platform_settings';
    $platform_enabled = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT platform_enabled FROM %i WHERE id = %d",
            $platform_table,
            $platform_id
        )
    );
    if (! $platform_enabled) {
        return false;
    }



    $table_name = $wpdb->prefix . 'unipixel_woocomm_event_settings';

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $query = $wpdb->prepare(
        "SELECT event_enabled FROM %i WHERE platform_id = %d AND event_platform_ref = %s",
        $table_name,
        $platform_id,
        $event_platform_ref
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $row = $wpdb->get_row($query);

    if (!$row) {
        return false;
    }

    return (bool) $row->event_enabled;
}


function unipixel_woo_event_get_settings($platform_id, $event_platform_ref)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'unipixel_woocomm_event_settings';

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE platform_id = %d AND event_platform_ref = %s",
        $table_name,
        $platform_id,
        $event_platform_ref
    );

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $row = $wpdb->get_row($query); // returns stdClass or null
    return $row ?: new stdClass(); // ensure it always returns an object
}




