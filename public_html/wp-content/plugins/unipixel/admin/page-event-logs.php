<?php

//File: public_html\wp-content\plugins\unipixel\admin\page-event-logs.php

if (!defined('ABSPATH')) exit;

function unipixel_page_event_logs()
{
    global $wpdb;

    $log_table = $wpdb->prefix . 'unipixel_event_log';

    // -----------------------------
    // 1) Read + sanitize filters
    // -----------------------------
    $platform_filter = '';
    if (isset($_GET['unipixel_platform']) && is_string($_GET['unipixel_platform'])) {
        $platform_filter = sanitize_text_field(wp_unslash($_GET['unipixel_platform']));
    }

    $event_filter = '';
    if (isset($_GET['unipixel_event']) && is_string($_GET['unipixel_event'])) {
        $event_filter = sanitize_text_field(wp_unslash($_GET['unipixel_event']));
    }

    // Optional (but useful for debugging)
    $method_filter = '';
    if (isset($_GET['unipixel_method']) && is_string($_GET['unipixel_method'])) {
        $method_filter = sanitize_text_field(wp_unslash($_GET['unipixel_method']));
    }

    $party_filter = '';
    if (isset($_GET['unipixel_party']) && is_string($_GET['unipixel_party'])) {
        $party_filter = sanitize_text_field(wp_unslash($_GET['unipixel_party']));
    }

    // -----------------------------
    // 2) Fetch platform list for dropdown
    // -----------------------------
    $platform_query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT DISTINCT platform_name FROM %i ORDER BY platform_name ASC",
        $log_table
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $platform_rows = $wpdb->get_col($platform_query);

    if (!is_array($platform_rows)) {
        $platform_rows = [];
    }

    // -----------------------------
    // 3) Build WHERE clause dynamically (prepared)
    // -----------------------------
    $where_sql = "WHERE 1=1";
    $where_args = [];

    if ($platform_filter !== '') {
        $where_sql .= " AND platform_name = %s";
        $where_args[] = $platform_filter;
    }

    if ($event_filter !== '') {
        // partial match, case insensitive depends on collation (usually utf8mb4_general_ci)
        $where_sql .= " AND event_name LIKE %s";
        $where_args[] = '%' . $wpdb->esc_like($event_filter) . '%';
    }

    if ($method_filter !== '') {
        $where_sql .= " AND method = %s";
        $where_args[] = $method_filter;
    }

    if ($party_filter !== '') {
        $where_sql .= " AND party = %s";
        $where_args[] = $party_filter;
    }

    // -----------------------------
    // 4) Final query: order + limit AFTER filtering
    // -----------------------------
    $base_sql =
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i {$where_sql} ORDER BY log_time DESC LIMIT %d";

    // Build the args for prepare: table name first, then WHERE args, then limit
    $prepare_args = [];
    $prepare_args[] = $log_table;

    foreach ($where_args as $arg) {
        $prepare_args[] = $arg;
    }

    $prepare_args[] = 200;

    // Prepare query safely
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $query = call_user_func_array([$wpdb, 'prepare'], array_merge([$base_sql], $prepare_args));

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $logs = $wpdb->get_results($query, ARRAY_A);

    if (!is_array($logs)) {
        $logs = [];
    }

    // -----------------------------
    // 5) Timezone setup
    // -----------------------------
    $timezone_string = get_option('timezone_string');
    if (empty($timezone_string)) {
        $gmt_offset = get_option('gmt_offset');
        $timezone_string = $gmt_offset ? timezone_name_from_abbr('', $gmt_offset * 3600, 0) : 'UTC';
    }

    // URL for form action (keeps it on the same admin page)
    $action_url = menu_page_url('unipixel_event_logs', false);
?>
    <div class="UniPixelShell pt-4">
        <div class="d-flex justify-content-between align-items-start">
            <h1 class="mb-3"><h2><i class="fa-solid fa-database"></i> Stored Event Logs</h2></h1>
            <?php unipixel_render_feedback_buttons(); ?>
        </div>

        <p class="text-muted mb-4">
            These logs show all events that UniPixel has stored in your database after being sent to connected platforms like Meta and Google.
            Events are stored when database storage is enabled in your
            <a href="<?php echo esc_url(menu_page_url('unipixel_general_settings', false)); ?>">General Settings</a>.
        </p>

        <!-- Filters (server-side) -->
        <form method="get" action="<?php echo esc_url($action_url); ?>" class="mb-3">
            <!-- Preserve the admin page slug -->
            <input type="hidden" name="page" value="unipixel_event_logs" />

            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label mb-1" for="unipixel_platform">Platform</label>
                    <select class="form-select form-select-sm" id="unipixel_platform" name="unipixel_platform">
                        <option value="">All platforms</option>
                        <?php foreach ($platform_rows as $pname) : ?>
                            <option value="<?php echo esc_attr($pname); ?>" <?php selected($platform_filter, $pname); ?>>
                                <?php echo esc_html($pname); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label mb-1" for="unipixel_event">Event name contains</label>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        id="unipixel_event"
                        name="unipixel_event"
                        value="<?php echo esc_attr($event_filter); ?>"
                        placeholder="e.g. add_to_cart"
                    />
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="unipixel_method">Method</label>
                    <select class="form-select form-select-sm" id="unipixel_method" name="unipixel_method">
                        <option value="">All</option>
                        <option value="client" <?php selected($method_filter, 'client'); ?>>client</option>
                        <option value="server" <?php selected($method_filter, 'server'); ?>>server</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="unipixel_party">Party</label>
                    <select class="form-select form-select-sm" id="unipixel_party" name="unipixel_party">
                        <option value="">All</option>
                        <option value="first" <?php selected($party_filter, 'first'); ?>>first</option>
                        <option value="third" <?php selected($party_filter, 'third'); ?>>third</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    <a class="btn btn-sm btn-outline-secondary w-100" href="<?php echo esc_url($action_url); ?>">Clear</a>
                </div>
            </div>

            <p class="small text-muted mt-2 mb-0">
                Showing up to the 200 most recent logs matching your filters.
            </p>
        </form>

        <?php if (empty($logs)) : ?>
            <div class="alert alert-info">No stored events found for the current filters.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Log Time</th>
                            <th>Event Name</th>
                            <th>Platform</th>
                            <th>Send Method</th>
                            <th>Party</th>
                            <th>Event Trigger</th>
                            <th>Response</th>
                            <th>Sent Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log) : ?>
                            <?php
                            $utc_time = new DateTime($log['log_time'], new DateTimeZone('UTC'));
                            $local_time = $utc_time->setTimezone(new DateTimeZone($timezone_string));

                            $sent_data = isset($log['sent_data']) ? (string) $log['sent_data'] : '';
                            $pretty_sent_data = str_replace(['\"', '\\'], '"', $sent_data);
                            ?>
                            <tr>
                                <td><?php echo esc_html($local_time->format('Y-m-d H:i:s')); ?></td>
                                <td><?php echo esc_html($log['event_name']); ?></td>
                                <td><?php echo esc_html($log['platform_name']); ?></td>
                                <td><?php echo esc_html($log['method']); ?></td>
                                <td><?php echo esc_html($log['party']); ?></td>
                                <td><?php echo esc_html($log['event_trigger']); ?></td>
                                <td><?php echo esc_html($log['response_message']); ?></td>
                                <td>
                                    <i class="fas fa-info-circle text-secondary" role="button"
                                       data-bs-toggle="popover"
                                       data-bs-title="Sent Data"
                                       data-bs-html="true"
                                       data-bs-content="<?php echo esc_attr('<pre style=\'white-space: pre-wrap; word-break: break-all;\'>' . htmlspecialchars($pretty_sent_data, ENT_QUOTES, 'UTF-8') . '</pre>'); ?>"></i>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (el) {
                return new bootstrap.Popover(el, { html: true });
            });
        });
    </script>
<?php
}
