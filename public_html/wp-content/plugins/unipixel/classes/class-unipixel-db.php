<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class UniPixelDB {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'unipixel_events_settings';
    }

    public function create_event($platform_id, $element_ref, $event_trigger, $event_name, $event_description) {
        $data = [
            'platform_id' => $platform_id,
            'element_ref' => $element_ref,
            'event_trigger' => $event_trigger,
            'event_name' => $event_name,
            'event_description' => $event_description,
        ];

        $format = ['%d', '%s', '%s', '%s', '%s'];

        return $this->wpdb->insert($this->table_name, $data, $format);
    }

    public function get_event($id) {
        // Concatenate the table name directly
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $query = $this->wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $this->table_name,
            $id
        );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_row($query, ARRAY_A);
    }
    
    public function update_event($id, $data) {
        $format = [];
        foreach ($data as $key => $value) {
            $format[] = is_int($value) ? '%d' : '%s';
        }

        $where = ['id' => $id];
        $where_format = ['%d'];

        return $this->wpdb->update($this->table_name, $data, $where, $format, $where_format);
    }

    public function delete_event($id) {
        $where = ['id' => $id];
        $where_format = ['%d'];

        return $this->wpdb->delete($this->table_name, $where, $where_format);
    }

    public function get_events_by_platform($platform_id) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $query = $this->wpdb->prepare(
            "SELECT * FROM %i WHERE platform_id = %d", 
            $this->table_name, 
            $platform_id
    );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_results($query, ARRAY_A);
    }
}
