<?php
// File: public_html/wp-content/plugins/unipixel/admin/handlers/handler-conversion-groups.php
//
// Phase 3 — Conversion groups CRUD + supporting endpoints (enabled platforms list, page list).
// All endpoints require the standard unipixel_ajax_nonce.

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_unipixel_conversions_list',     'unipixel_handle_conversions_list');
add_action('wp_ajax_unipixel_conversions_get',      'unipixel_handle_conversions_get');
add_action('wp_ajax_unipixel_conversions_create',   'unipixel_handle_conversions_create');
add_action('wp_ajax_unipixel_conversions_update',   'unipixel_handle_conversions_update');
add_action('wp_ajax_unipixel_conversions_delete',   'unipixel_handle_conversions_delete');
add_action('wp_ajax_unipixel_conversions_platforms','unipixel_handle_conversions_platforms');
add_action('wp_ajax_unipixel_conversions_pages',    'unipixel_handle_conversions_pages');

/**
 * Conceptual event → per-platform standard event name.
 * Used as the suggested default when the user picks a conceptual event in the builder.
 * The user can still override per-platform names in the builder UI.
 */
function unipixel_conceptual_event_map() {
    return [
        'Lead'                 => ['1' => 'Lead',                 '2' => 'Lead',     '3' => 'Contact',              '4' => 'generate_lead', '5' => 'lead'],
        'ContactFormSubmitted' => ['1' => 'Contact',              '2' => 'Lead',     '3' => 'Contact',              '4' => 'generate_lead', '5' => 'contact'],
        'NewsletterSignup'     => ['1' => 'Subscribe',            '2' => 'Signup',   '3' => 'Subscribe',            '4' => 'sign_up',       '5' => 'subscribe'],
        'Registration'         => ['1' => 'CompleteRegistration', '2' => 'Signup',   '3' => 'CompleteRegistration', '4' => 'sign_up',       '5' => 'sign_up'],
        'Search'               => ['1' => 'Search',               '2' => 'Search',   '3' => 'Search',               '4' => 'search',        '5' => 'search'],
        'ViewContent'          => ['1' => 'ViewContent',          '2' => 'PageVisit','3' => 'ViewContent',          '4' => 'view_item',     '5' => 'view_item'],
    ];
}

function unipixel_conversions_check_nonce() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unipixel_ajax_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }
}

function unipixel_handle_conversions_list() {
    unipixel_conversions_check_nonce();
    global $wpdb;
    $groups_table = $wpdb->prefix . 'unipixel_conversion_groups';
    $events_table = $wpdb->prefix . 'unipixel_events_settings';
    $platforms_table = $wpdb->prefix . 'unipixel_platform_settings';

    $groups = $wpdb->get_results("SELECT * FROM {$groups_table} ORDER BY id DESC", ARRAY_A);
    $platforms_rows = $wpdb->get_results(
        "SELECT id, platform_name, platform_enabled FROM {$platforms_table} ORDER BY id ASC",
        ARRAY_A
    );
    $platform_admin_slugs = ['1' => 'unipixel_meta', '2' => 'unipixel_pinterest', '3' => 'unipixel_tiktok', '4' => 'unipixel_google', '5' => 'unipixel_microsoft'];
    $platforms = [];
    $enabled_count = 0;
    foreach ($platforms_rows as $r) {
        $is_enabled = (int)$r['platform_enabled'] === 1;
        if ($is_enabled) $enabled_count++;
        $platforms[] = [
            'id' => (int)$r['id'],
            'platform_name' => $r['platform_name'],
            'enabled' => $is_enabled,
            'admin_url' => isset($platform_admin_slugs[(string)$r['id']])
                ? admin_url('admin.php?page=' . $platform_admin_slugs[(string)$r['id']])
                : '',
        ];
    }

    foreach ($groups as &$g) {
        $g['linked_rows'] = $wpdb->get_results($wpdb->prepare(
            "SELECT e.platform_id, p.platform_name, e.event_name, e.send_client, e.send_server
             FROM {$events_table} e
             LEFT JOIN {$platforms_table} p ON p.id = e.platform_id
             WHERE e.conversion_group_id = %d",
            $g['id']
        ), ARRAY_A);
        $g['platform_count'] = count($g['linked_rows']);
        $g['enabled_platform_count'] = $enabled_count;
    }
    unset($g);

    wp_send_json_success([
        'groups' => $groups,
        'enabled_platform_count' => $enabled_count,
        'platforms' => $platforms,
    ]);
}

function unipixel_handle_conversions_get() {
    unipixel_conversions_check_nonce();
    global $wpdb;
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    if ($group_id <= 0) {
        wp_send_json_error(['message' => 'Invalid group_id']);
    }
    $groups_table = $wpdb->prefix . 'unipixel_conversion_groups';
    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    $group = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$groups_table} WHERE id = %d", $group_id), ARRAY_A);
    if (!$group) {
        wp_send_json_error(['message' => 'Group not found']);
    }
    $group['linked_rows'] = $wpdb->get_results($wpdb->prepare(
        "SELECT id, platform_id, event_name, send_client, send_server, send_server_log_response
         FROM {$events_table} WHERE conversion_group_id = %d",
        $group_id
    ), ARRAY_A);

    wp_send_json_success(['group' => $group]);
}

/**
 * Sanitize platform rows from POST input. Each must have platform_id and event_name.
 * Returns an array of validated platform row objects.
 */
function unipixel_conversions_sanitize_platforms($raw) {
    if (!is_array($raw)) return [];
    $out = [];
    foreach ($raw as $p) {
        if (!is_array($p)) continue;
        $platform_id = isset($p['platform_id']) ? intval($p['platform_id']) : 0;
        $event_name = isset($p['event_name']) ? sanitize_text_field($p['event_name']) : '';
        $include = isset($p['include']) && ($p['include'] === '1' || $p['include'] === 1 || $p['include'] === true || $p['include'] === 'true');
        if ($platform_id <= 0 || $event_name === '' || !$include) continue;
        $out[] = [
            'platform_id' => $platform_id,
            'event_name'  => $event_name,
            'send_client' => !empty($p['send_client']) ? 1 : 0,
            'send_server' => !empty($p['send_server']) ? 1 : 0,
            'send_server_log_response' => !empty($p['send_server_log_response']) ? 1 : 0,
        ];
    }
    return $out;
}

function unipixel_handle_conversions_create() {
    unipixel_conversions_check_nonce();
    global $wpdb;

    $trigger_type    = isset($_POST['event_trigger']) ? sanitize_text_field($_POST['event_trigger']) : '';
    $trigger_target  = isset($_POST['trigger_target']) ? sanitize_text_field(wp_unslash($_POST['trigger_target'])) : '';
    $conceptual      = isset($_POST['conceptual_event']) ? sanitize_text_field($_POST['conceptual_event']) : '';
    $description     = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
    $platforms_raw   = isset($_POST['platforms']) ? $_POST['platforms'] : [];

    if (!in_array($trigger_type, ['click', 'shown', 'url'], true) || $trigger_target === '' || $conceptual === '') {
        wp_send_json_error(['message' => 'Missing required fields']);
    }

    $platforms = unipixel_conversions_sanitize_platforms($platforms_raw);
    if (empty($platforms)) {
        wp_send_json_error(['message' => 'No platforms included']);
    }

    $groups_table = $wpdb->prefix . 'unipixel_conversion_groups';
    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    $wpdb->insert($groups_table, [
        'conceptual_event' => $conceptual,
        'description'      => $description,
        'event_trigger'    => $trigger_type,
        'trigger_target'   => $trigger_target,
    ]);
    $group_id = (int) $wpdb->insert_id;
    if ($group_id <= 0) {
        wp_send_json_error(['message' => 'Failed to create group: ' . $wpdb->last_error]);
    }

    foreach ($platforms as $p) {
        $wpdb->insert($events_table, [
            'platform_id'              => $p['platform_id'],
            'element_ref'              => $trigger_target,
            'event_trigger'            => $trigger_type,
            'event_name'               => $p['event_name'],
            'event_description'        => $description,
            'send_client'              => $p['send_client'],
            'send_server'              => $p['send_server'],
            'send_server_log_response' => $p['send_server_log_response'],
            'conversion_group_id'      => $group_id,
        ]);
    }

    wp_send_json_success(['group_id' => $group_id]);
}

function unipixel_handle_conversions_update() {
    unipixel_conversions_check_nonce();
    global $wpdb;

    $group_id        = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $trigger_type    = isset($_POST['event_trigger']) ? sanitize_text_field($_POST['event_trigger']) : '';
    $trigger_target  = isset($_POST['trigger_target']) ? sanitize_text_field(wp_unslash($_POST['trigger_target'])) : '';
    $conceptual      = isset($_POST['conceptual_event']) ? sanitize_text_field($_POST['conceptual_event']) : '';
    $description     = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
    $platforms_raw   = isset($_POST['platforms']) ? $_POST['platforms'] : [];

    if ($group_id <= 0 || !in_array($trigger_type, ['click', 'shown', 'url'], true) || $trigger_target === '' || $conceptual === '') {
        wp_send_json_error(['message' => 'Missing or invalid fields']);
    }

    $platforms = unipixel_conversions_sanitize_platforms($platforms_raw);
    $groups_table = $wpdb->prefix . 'unipixel_conversion_groups';
    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    $wpdb->update($groups_table, [
        'conceptual_event' => $conceptual,
        'description'      => $description,
        'event_trigger'    => $trigger_type,
        'trigger_target'   => $trigger_target,
    ], ['id' => $group_id]);

    // Sync events_settings rows for this group to the new platform list.
    $existing = $wpdb->get_results($wpdb->prepare(
        "SELECT id, platform_id FROM {$events_table} WHERE conversion_group_id = %d",
        $group_id
    ), ARRAY_A);
    $existing_by_platform = [];
    foreach ($existing as $row) {
        $existing_by_platform[(int)$row['platform_id']] = (int)$row['id'];
    }

    $new_platform_ids = array_map(function($p){ return (int)$p['platform_id']; }, $platforms);

    foreach ($platforms as $p) {
        if (isset($existing_by_platform[$p['platform_id']])) {
            $wpdb->update($events_table, [
                'element_ref'              => $trigger_target,
                'event_trigger'            => $trigger_type,
                'event_name'               => $p['event_name'],
                'event_description'        => $description,
                'send_client'              => $p['send_client'],
                'send_server'              => $p['send_server'],
                'send_server_log_response' => $p['send_server_log_response'],
            ], ['id' => $existing_by_platform[$p['platform_id']]]);
        } else {
            $wpdb->insert($events_table, [
                'platform_id'              => $p['platform_id'],
                'element_ref'              => $trigger_target,
                'event_trigger'            => $trigger_type,
                'event_name'               => $p['event_name'],
                'event_description'        => $description,
                'send_client'              => $p['send_client'],
                'send_server'              => $p['send_server'],
                'send_server_log_response' => $p['send_server_log_response'],
                'conversion_group_id'      => $group_id,
            ]);
        }
    }

    // Delete rows for platforms no longer included.
    foreach ($existing_by_platform as $platform_id => $row_id) {
        if (!in_array($platform_id, $new_platform_ids, true)) {
            $wpdb->delete($events_table, ['id' => $row_id]);
        }
    }

    wp_send_json_success(['group_id' => $group_id]);
}

function unipixel_handle_conversions_delete() {
    unipixel_conversions_check_nonce();
    global $wpdb;
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $unlink_only = !empty($_POST['unlink_only']);
    if ($group_id <= 0) {
        wp_send_json_error(['message' => 'Invalid group_id']);
    }
    $groups_table = $wpdb->prefix . 'unipixel_conversion_groups';
    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    if ($unlink_only) {
        $wpdb->update($events_table, ['conversion_group_id' => null], ['conversion_group_id' => $group_id]);
    } else {
        $wpdb->delete($events_table, ['conversion_group_id' => $group_id]);
    }
    $wpdb->delete($groups_table, ['id' => $group_id]);

    wp_send_json_success(['deleted' => true, 'unlink_only' => $unlink_only]);
}

function unipixel_handle_conversions_platforms() {
    unipixel_conversions_check_nonce();
    global $wpdb;
    $platforms_table = $wpdb->prefix . 'unipixel_platform_settings';
    $rows = $wpdb->get_results(
        "SELECT id, platform_name, platform_enabled, serverside_global_enabled
         FROM {$platforms_table}
         ORDER BY id ASC",
        ARRAY_A
    );
    $platform_admin_slugs = ['1' => 'unipixel_meta', '2' => 'unipixel_pinterest', '3' => 'unipixel_tiktok', '4' => 'unipixel_google', '5' => 'unipixel_microsoft'];
    foreach ($rows as &$r) {
        $r['enabled'] = (int)$r['platform_enabled'] === 1;
        $r['admin_url'] = isset($platform_admin_slugs[(string)$r['id']])
            ? admin_url('admin.php?page=' . $platform_admin_slugs[(string)$r['id']])
            : '';
    }
    unset($r);

    wp_send_json_success([
        'platforms' => $rows,
        'conceptual_event_map' => unipixel_conceptual_event_map(),
    ]);
}

function unipixel_handle_conversions_pages() {
    unipixel_conversions_check_nonce();
    $pages = get_posts([
        'post_type'   => ['page', 'post'],
        'post_status' => 'publish',
        'numberposts' => 200,
        'orderby'     => 'title',
        'order'       => 'ASC',
    ]);
    $out = [];
    foreach ($pages as $p) {
        $url = parse_url(get_permalink($p->ID));
        $path = isset($url['path']) ? $url['path'] : '/';
        $out[] = [
            'id'    => $p->ID,
            'title' => $p->post_title,
            'path'  => $path,
            'type'  => $p->post_type,
        ];
    }
    wp_send_json_success(['pages' => $out]);
}
