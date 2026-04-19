<?php
/**
 * Plugin Name: UnipixelEmailAPI
 * Plugin URI:  https://none.com
 * Description: An email sending service API that validates a specific email template and payload, then sends email using your mail library.
 * Version:     1.1
 * Author:      Buildio
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register REST API Endpoint:
 * Endpoint URL: POST /wp-json/unipixelemailapi/v1/send
 */
add_action( 'rest_api_init', function() {
    register_rest_route( 'unipixelemailapi/v1', '/send', [
        'methods'             => 'POST',
        'callback'            => 'unipixelemailapi_handle_request',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Helper function: Get the User IP Address.
 *
 * This function retrieves the IP address from various server variables.
 */
function unipixelemailapi_get_user_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        return sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
        return sanitize_text_field( trim( $ips[0] ) );
    }
    return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
}

/**
 * Handle the incoming REST API request.
 *
 * Expected JSON structure:
 * {
 *   "emailTemplateId": 3859206,
 *   "payload": {
 *      "FeedbackType": "Type example",
 *      "FeedbackDescription": "Description example",
 *      "UserEmail": "user@example.com" // optional
 *   },
 *   "referring_ip": "192.168.1.1",      // optional; if not provided, the client’s IP is used
 *   "referring_url": "https://example.com/page" // optional
 * }
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function unipixelemailapi_handle_request( WP_REST_Request $request ) {
    $params = $request->get_json_params();


    $emailTemplateId = 3859206;

    // Validate emailTemplateId.
    if ( ! isset( $params['emailTemplateId'] ) || intval( $params['emailTemplateId'] ) !== $emailTemplateId ) {
        return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Invalid id' ], 400 );
    }

    // Validate payload.
    if ( ! isset( $params['payload'] ) || ! is_array( $params['payload'] ) ) {
        return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Invalid payload.' ], 400 );
    }

    $payload = $params['payload'];

    // Validate required fields.
    if ( empty( $payload['FeedbackType'] ) || empty( $payload['FeedbackDescription'] ) ) {
        return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Missing required payload fields: FeedbackType and/or FeedbackDescription.' ], 400 );
    }

    // Sanitize and deconstruct payload.
    $feedbackType        = sanitize_text_field( $payload['FeedbackType'] );
    $feedbackDescription = sanitize_textarea_field( $payload['FeedbackDescription'] );
    $userEmail           = isset( $payload['UserEmail'] ) ? sanitize_email( $payload['UserEmail'] ) : '';

    // Accept referring IP and URL from the request.
    // If referring_ip is not provided, use the client’s IP.
    $referring_ip  = isset( $params['referring_ip'] ) ? sanitize_text_field( $params['referring_ip'] ) : unipixelemailapi_get_user_ip();
    $referring_url = isset( $params['referring_url'] ) ? esc_url_raw( $params['referring_url'] ) : '';

    // Set the email subject based on the feedback type.
    $subject = "Feedback Received: " . $feedbackType;

    // Build the email message body.
    $message  = "You have received new feedback.\n\n";
    $message .= "Feedback Type: " . $feedbackType . "\n";
    $message .= "Feedback Description: " . $feedbackDescription . "\n";
    if ( ! empty( $userEmail ) ) {
        $message .= "User Email: " . $userEmail . "\n";
    }
    $message .= "Referring IP: " . $referring_ip . "\n";
    if ( ! empty( $referring_url ) ) {
        $message .= "Referring URL: " . $referring_url . "\n";
    }

    $emailrecipients = array(
        'contact@buildio.dev',
    );

    wp_mail($emailrecipients, $subject, $message);

    // For demonstration, assume mail sending is successful.
    return new WP_REST_Response( [ 'status' => 'success', 'message' => 'Email processed successfully.' ], 200 );
}
