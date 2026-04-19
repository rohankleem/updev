<?php
//File: public_html\wp-content\plugins\unipixel\functions\ajax-handle-log-client-event.php

add_action('wp_ajax_unipixel_log_client_event', 'unipixel_log_client_event_handler');
add_action('wp_ajax_nopriv_unipixel_log_client_event', 'unipixel_log_client_event_handler');

function unipixel_log_client_event_handler() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(['message' => 'Invalid method.'], 405);
    }

    check_ajax_referer('unipixel_log_client_event_nonce', 'nonce');

    $platform_id   = isset($_POST['platform_id'])   ? sanitize_text_field(wp_unslash($_POST['platform_id']))   : '';
    $element_ref   = isset($_POST['element_ref'])   ? sanitize_text_field(wp_unslash($_POST['element_ref']))   : '';
    $event_trigger = isset($_POST['event_trigger']) ? sanitize_text_field(wp_unslash($_POST['event_trigger'])) : '';
    $event_name    = isset($_POST['event_name'])    ? sanitize_text_field(wp_unslash($_POST['event_name']))    : '';

    if ($platform_id === '' || $event_name === '') {
        wp_send_json_error(['message' => 'Missing required fields.'], 422);
    }

    // Use provided message, or default to client-side placeholder
    $responseLogMessage = isset($_POST['response_log_message']) && $_POST['response_log_message'] !== ''
        ? wp_kses_post(wp_unslash($_POST['response_log_message']))
        : 'Client-side event, no response';

    // JSON data
    $jsonDataSent = '';
    if (isset($_POST['json_data_sent'])) {
        $raw = wp_unslash($_POST['json_data_sent']);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $jsonDataSent = (json_last_error() === JSON_ERROR_NONE) ? wp_json_encode($decoded) : wp_json_encode(['raw' => $raw]);
        } else {
            $jsonDataSent = wp_json_encode($raw);
        }
    }

    $method = 'client';

    $party = 'third';
    if (isset($_POST['party'])) {
        $p = sanitize_text_field(wp_unslash($_POST['party']));
        if (in_array($p, ['first', 'third'], true)) { $party = $p; }
    }

    $event_order = 'clientFirst';
    if (isset($_POST['event_order'])) {
        $eo = sanitize_text_field(wp_unslash($_POST['event_order']));
        if (in_array($eo, ['clientFirst', 'clientSecond'], true)) { $event_order = $eo; }
    }

    try {
        if (!class_exists('UniPixelLog')) {
            wp_send_json_error(['message' => 'Logging class not available.'], 500);
        }

        $log = new UniPixelLog();
        $log_id = $log->insert_log(
            $platform_id,
            $element_ref,
            $event_trigger,
            $event_name,
            $responseLogMessage,
            $jsonDataSent,
            $method,
            $party,
            $event_order
        );

        wp_send_json_success(['log_id' => $log_id]);
    } catch (Throwable $e) {
        wp_send_json_error(['message' => 'Insert failed.', 'error' => $e->getMessage()], 500);
    }
}
