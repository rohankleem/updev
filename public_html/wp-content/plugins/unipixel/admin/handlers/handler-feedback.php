<?php

add_action('wp_ajax_unipixel_submit_feedback', 'unipixel_submit_feedback');

function unipixel_submit_feedback() {
    // Verify the nonce for security.
    check_ajax_referer('unipixel_feedback_nonce', 'nonce');

    // Sanitize input values from the form.
    $type    = sanitize_text_field($_POST['unipixel_feedback_type'] ?? '');
    $message = sanitize_textarea_field($_POST['unipixel_feedback'] ?? '');
    $email   = sanitize_email($_POST['unipixel_email'] ?? '');

    // Validate required fields.
    if ( empty( $message ) ) {
        wp_send_json_error('Message is required');
    }

    // Build JSON payload to send
    $data = array(
        'emailTemplateId' => 3859206,
        'payload'         => array(
            'FeedbackType'        => $type,
            'FeedbackDescription' => $message,
            'UserEmail'           => $email, // Optional
        ),
        'referring_ip'    => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
        'referring_url'   => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
    );

    // API endpoint URL.
    $api_url = 'https://buildio.dev/wp-json/unipixelemailapi/v1/send';

    // Prepare the arguments for the remote POST request.
    $args = array(
        'body'      => wp_json_encode($data),
        'headers'   => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'   => 15,
    );

    $response = wp_remote_post($api_url, $args);

    // Handle error responses.
    if ( is_wp_error( $response ) ) {
        wp_send_json_error('Email failed to send. API error: ' . $response->get_error_message());
    }

    // Decode the response.
    $response_body = wp_remote_retrieve_body( $response );
    $result = json_decode( $response_body, true );

    // Check the API response status.
    if ( isset($result['status']) && $result['status'] === "success" ) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Email failed to send');
    }
}
