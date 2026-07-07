<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler: clear all Stored Event Logs.
 *
 * Empties wp_unipixel_event_log and resets the wp_unipixel_log_count gauge to 0,
 * so the auto-prune trigger in UniPixelLog::insert_log() stays in sync with the
 * real row count. Manual counterpart to the automatic prune.
 */
add_action('wp_ajax_unipixel_clear_event_logs', 'unipixel_handle_clear_event_logs');

function unipixel_handle_clear_event_logs()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('You do not have permission to do this.', 'unipixel'),
        ), 403);
        return;
    }

    global $wpdb;
    $log_table   = $wpdb->prefix . 'unipixel_event_log';
    $count_table = $wpdb->prefix . 'unipixel_log_count';

    $wpdb->last_error = '';

    // TRUNCATE is the fast full wipe; then reset the prune gauge to the true (empty) count.
    $wpdb->query($wpdb->prepare("TRUNCATE TABLE %i", $log_table));
    $wpdb->query($wpdb->prepare("UPDATE %i SET count = 0 WHERE id = %d", $count_table, 1));

    if ($wpdb->last_error) {
        error_log('UniPixel clear event logs DB error: ' . $wpdb->last_error);
        wp_send_json_error(array(
            'message' => __('Could not clear the logs. Please try again.', 'unipixel'),
        ));
        return;
    }

    wp_send_json_success(array(
        'message' => __('Stored event logs deleted.', 'unipixel'),
    ));
}
