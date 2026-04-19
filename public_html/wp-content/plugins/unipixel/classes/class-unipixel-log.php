<?php

//File: public_html\wp-content\plugins\unipixel\classes\class-unipixel-log.php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class UniPixelLog
{

    private $log_table;
    private $log_count_table;

    public function __construct()
    {
        global $wpdb;
        $this->log_table = $wpdb->prefix . 'unipixel_event_log';
        $this->log_count_table = $wpdb->prefix . 'unipixel_log_count';
    }

    public function insert_log($platform_id, $element_ref, $event_trigger, $event_name, $response_message, $sent_data, $method = null, $party = null, $event_order = null)
    {

        if (!function_exists('unipixel_should_dbstore_event') || !unipixel_should_dbstore_event($event_name)) {
            return;
        }

        global $wpdb;

        $platform_name = unipixel_get_platform_name($platform_id);



        // 1) "Clean" $sent_data in place
        // If it's an array with a JSON body, decode+re-encode without escaped slashes;
        // otherwise just strip slashes from strings.
        // Clean and pretty-print sent_data for readable DB logs
        // Make sure sent_data is always an array first
        if (is_string($sent_data)) {
            $sent_data = json_decode($sent_data, true);
        }
        if (is_array($sent_data) && isset($sent_data['body'])) {
            $raw = $sent_data['body'];
            $decoded = json_decode(stripslashes($raw), true);
            if ($decoded !== null) {
                $sent_data['body'] = $decoded;
            } else {
                $sent_data['body'] = stripslashes($raw);
            }
        }
        if (is_array($sent_data)) {
            $sent_data = json_encode(
                $sent_data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }


        // ------------------------


        // Base log data
        $log_data = [
            'platform_id'      => $platform_id,
            'platform_name'    => $platform_name,
            'element_ref'      => $element_ref,
            'event_trigger'    => $event_trigger,
            'event_name'       => $event_name,
            'response_message' => $response_message,
            'sent_data'        => $sent_data,
        ];

        $log_format = [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ];



        // Optionally add new fields if present
        if (!is_null($method)) {
            $log_data['method'] = $method;
            $log_format[] = '%s';
        }

        if (!is_null($party)) {
            $log_data['party'] = $party;
            $log_format[] = '%s';
        }

        if (!is_null($event_order)) {
            $log_data['event_order'] = $event_order;
            $log_format[] = '%s';
        }


        // Insert log
        $wpdb->insert($this->log_table, $log_data, $log_format);

        // Increment the log count
        $query = $wpdb->prepare(
            "UPDATE %i SET count = count + 1 WHERE id = %d",
            $this->log_count_table,
            1
        );
        $wpdb->query($query);

        // Auto-prune logs if needed
        $logTriggerDeleteQty = 50000;
        $logMinKeepQty = 40000;
        $logToDeleteQty = 10000;

        $query = $wpdb->prepare(
            "SELECT count FROM %i WHERE id = %d",
            $this->log_count_table,
            1
        );
        $current_count = $wpdb->get_var($query);

        if ($current_count >= $logTriggerDeleteQty) {
            $this->cleanup_logs($logMinKeepQty, $logToDeleteQty);
        }
    }


    private function cleanup_logs($logMinKeepQty, $logToDeleteQty)
    {
        global $wpdb;

        // Fetch IDs of the oldest logs to delete
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $query = $wpdb->prepare(
            "SELECT id FROM %i ORDER BY log_time ASC LIMIT %d",
            $this->log_table,
            $logToDeleteQty
        );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $logs_to_delete = $wpdb->get_col($query);

        // Delete the logs in one go
        if (!empty($logs_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($logs_to_delete), '%d'));
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $query = $wpdb->prepare(
                "DELETE FROM %i WHERE id IN ($placeholders)",
                $this->log_table,
                $logs_to_delete
            );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query($query);

            // Update the log count
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM %i",
                $this->log_table
            );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $remaining_logs = $wpdb->get_var($query);
            $new_count = $remaining_logs > $logMinKeepQty ? $remaining_logs - $logMinKeepQty : 0;


            $query = $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "UPDATE %i SET count = %d WHERE id = %d",
                $this->log_count_table,
                $new_count,
                1
            );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query($query);
        }
    }
}
