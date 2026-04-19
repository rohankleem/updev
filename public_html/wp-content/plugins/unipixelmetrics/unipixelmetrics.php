<?php
/**
 * Plugin Name: UnipixelMetrics
 * Plugin URI:  https://example.com
 * Description: Receives usage logs (including IP and custom detail) from remote Unipixel plugin sites.
 * Version:     1.0
 * Author:      Your Name
 * License:     GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * 1. On Plugin Activation, Create/Upgrade the Table
 */
function unipixelmetrics_on_activate() {
    global $wpdb;

    $table_name      = $wpdb->prefix . 'unipixelmetrics_logs'; 
    $charset_collate = $wpdb->get_charset_collate();

    // dbDelta can add or update columns if the table already exists
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        site_id VARCHAR(255) NOT NULL,
        plugin_version VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        platform VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) DEFAULT '',
        detail LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'unipixelmetrics_on_activate');

/**
 * 2. Register a Custom REST Endpoint
 *    Endpoint: POST /wp-json/unipixelmetrics/v1/log
 */
add_action('rest_api_init', function () {
    register_rest_route('unipixelmetrics/v1', '/log', [
        'methods'             => 'POST',
        'callback'            => 'unipixelmetrics_handle_request',
        // If you want to restrict usage, replace with a function that checks a token, etc.
        'permission_callback' => '__return_true',
    ]);
});

/**
 * 3. Handle Incoming Requests
 */
function unipixelmetrics_handle_request(WP_REST_Request $request) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'unipixelmetrics_logs';
    $params     = $request->get_json_params();

    // 3A. Gather & sanitize basic fields
    $site_id        = isset($params['site_id'])        ? sanitize_text_field($params['site_id'])        : '';
    $plugin_version = isset($params['plugin_version']) ? sanitize_text_field($params['plugin_version']) : '';
    $action         = isset($params['action'])         ? sanitize_text_field($params['action'])         : '';
    $platform       = isset($params['platform'])       ? sanitize_text_field($params['platform'])       : '';

    // 3B. Capture detail (could be a string or JSON).
    $detail = '';
    if (isset($params['detail'])) {
        // For a simple string:
        $detail = sanitize_textarea_field(wp_unslash($params['detail']));

        // If 'detail' might be an array/object, you could do:
        // $detail = wp_json_encode($params['detail']);
    }

    // 3C. Determine the user IP
    $ip_address = unipixelmetrics_get_user_ip();

    // 3D. Insert the data into the logs table
    $wpdb->insert(
        $table_name,
        [
            'site_id'        => $site_id,
            'plugin_version' => $plugin_version,
            'action'         => $action,
            'platform'       => $platform,
            'ip_address'     => $ip_address,
            'detail'         => $detail,
        ],
        ['%s','%s','%s','%s','%s','%s']
    );

    // 3E. Return a response
    if ($wpdb->last_error) {
        return new WP_REST_Response(['status' => 'error', 'message' => $wpdb->last_error], 500);
    }

    return new WP_REST_Response(['status' => 'success'], 200);
}


/**
 * 4. Helper Function: Get the User IP
 */
function unipixelmetrics_get_user_ip() {
    // Basic approach; could also handle proxies, etc.
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}

/**
 * 5. (Optional) Create an Admin Page to View Logs
 */
add_action('admin_menu', 'unipixelmetrics_admin_menu');
function unipixelmetrics_admin_menu() {
    add_menu_page(
        'Unipixel Metrics',          // Page Title
        'Unipixel Metrics',          // Menu Title
        'manage_options',            // Capability
        'unipixelmetrics-logs',      // Menu Slug
        'unipixelmetrics_admin_page',// Callback
        'dashicons-visibility',      // Icon
        80                           // Position
    );
}

/**
 * 6. Render the Admin Page
 */
function unipixelmetrics_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixelmetrics_logs';

    // Fetch the 50 most recent logs, for instance
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 50");

    echo '<div class="wrap">';
    echo '<h1>Unipixel Metrics Logs</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>ID</th>
                <th>Site Id</th>
                <th>Plugin Version</th>
                <th>Action</th>
                <th>Platform</th>
                <th>IP Address</th>
                <th>Detail</th>
                <th>Created At</th>
            </tr>
          </thead>';
    echo '<tbody>';
    if (!empty($results)) {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->site_id) . '</td>';
            echo '<td>' . esc_html($row->plugin_version) . '</td>';
            echo '<td>' . esc_html($row->action) . '</td>';
            echo '<td>' . esc_html($row->platform) . '</td>';
            echo '<td>' . esc_html($row->ip_address) . '</td>';
            // If you stored JSON in 'detail', you might decode here:
            // $detail_data = json_decode($row->detail, true);
            // echo '<td>' . esc_html(print_r($detail_data, true)) . '</td>';
            echo '<td>' . esc_html($row->detail) . '</td>';
            echo '<td>' . esc_html($row->created_at) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8">No logs found.</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
