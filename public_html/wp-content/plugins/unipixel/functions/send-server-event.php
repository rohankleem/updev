<?php

// File: public_html\wp-content\plugins\unipixel\functions\send-server-event.php

function unipixel_send_server_event_meta($event_name, $user_data, $custom_data, $event_id, $eventTime_in = null, $pageUrl = '', $sendServerLogResponse = false, $consentAlreadyChecked = false)
{

    if (!$consentAlreadyChecked) {
        $consentBlocked = unipixel_check_for_consent();
        if ($consentBlocked) {
            return $consentBlocked;
        }
    }

    $platformId       = 1;
    $platformSettings = unipixel_get_platform_settings($platformId);

    $meta_pixel_id = $platformSettings->pixel_id ?? '';
    $access_token  = $platformSettings->access_token ?? '';

    $eventTime = !empty($eventTime_in) ? $eventTime_in : time();

    $eventInfoPayloadArr = [
        'event_name'       => $event_name,
        'event_time'       => $eventTime,
        'action_source'    => 'website',
        'event_id'         => $event_id,
        'event_source_url' => $pageUrl,
        'user_data'        => $user_data,
    ];
    if (!empty($custom_data)) {
        $eventInfoPayloadArr['custom_data'] = $custom_data;
    }

    $body = [
        'data'         => [$eventInfoPayloadArr],
        'access_token' => $access_token,
    ];

    // Keep external name `$args` for compatibility
    $wpRemotePostSendArr = [
        'body'    => wp_json_encode($body),
        'headers' => ['Content-Type' => 'application/json'],
        'method'  => 'POST',
    ];

    $endpoint = "https://graph.facebook.com/v14.0/{$meta_pixel_id}/events";

    // Internal name clarifies meaning; will be returned as ['response' => ...]
    $wpRemotePostResult = [
        'response' => [
            'code'    => 0,
            'message' => '',
        ],
    ];

    if ($sendServerLogResponse) {
        $wpRemotePostSendArr['timeout'] = 2.5;
        $wpRemotePostResult = wp_remote_post($endpoint, $wpRemotePostSendArr);
    } else {
        $wpRemotePostSendArr['timeout']  = 0.1;
        $wpRemotePostSendArr['blocking'] = false;
        wp_remote_post($endpoint, $wpRemotePostSendArr);
        // $wpRemotePostResult stays as the stub (code 0, empty message)
    }

    $sendServerResultInfo = [
        'response' => $wpRemotePostResult,
        'args'     => $wpRemotePostSendArr,
        'payload'  => $eventInfoPayloadArr,
    ];

    return $sendServerResultInfo;
}



function unipixel_send_server_event_google(
    $event_name,
    $user_data,
    $custom_data,
    $event_id,
    $sendServerLogResponse = false,
    $consentAlreadyChecked = false
) {
    if (!$consentAlreadyChecked) {
        $consentBlocked = unipixel_check_for_consent();
        if ($consentBlocked) {
            return $consentBlocked;
        }
    }

    $platformId       = 4;
    $platformSettings = unipixel_get_platform_settings($platformId);

    $measurement_id = $platformSettings->pixel_id ?? '';
    $api_secret     = $platformSettings->access_token ?? '';

    // Build event info
    $eventInfoPayloadArr = [
        'client_id' => $user_data['client_id'] ?? '',
        'events'    => [[
            'name'   => $event_name,
            'params' => array_merge($custom_data, [
                'engagement_time_msec' => 1000,
                'event_id'             => $event_id,
            ]),
        ]],
    ];

    // Add optional debug flag
    $opts = get_option('unipixel_logging_options', []);
    if (!empty($opts['enableGoogleDebugViewServerSide'])) {
        $eventInfoPayloadArr['events'][0]['params']['debug_mode'] = true;
    }

    // Prepare request data for wp_remote_post()
    $wpRemotePostSendArr = [
        'body'    => wp_json_encode($eventInfoPayloadArr),
        'headers' => ['Content-Type' => 'application/json'],
        'method'  => 'POST',
    ];

    $endpoint = "https://www.google-analytics.com/mp/collect?measurement_id={$measurement_id}&api_secret={$api_secret}";

    $wpRemotePostResult = [
        'response' => [
            'code'    => 0,
            'message' => '',
        ],
    ];

    if ($sendServerLogResponse) {
        $wpRemotePostSendArr['timeout'] = 2.5;
        $wpRemotePostResult = wp_remote_post($endpoint, $wpRemotePostSendArr);
    } else {
        $wpRemotePostSendArr['timeout']  = 0.1;
        $wpRemotePostSendArr['blocking'] = false;
        wp_remote_post($endpoint, $wpRemotePostSendArr);
    }

    $sendServerResultInfo = [
        'response' => $wpRemotePostResult,
        'args'     => $wpRemotePostSendArr,
        'payload'  => $eventInfoPayloadArr,
    ];

    return $sendServerResultInfo;
}



/**
 * SHA-256 hash helper for Pinterest PII fields.
 * Trims, lowercases, then hashes. Returns empty string if input is empty.
 */
function unipixel_sha256_hash_if_not_empty($value)
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    return hash('sha256', strtolower($value));
}


function unipixel_send_server_event_pinterest(
    $event_name,
    $user_data,
    $custom_data,
    $event_id,
    $eventTime_in = null,
    $pageUrl = '',
    $sendServerLogResponse = false,
    $consentAlreadyChecked = false
) {
    if (!$consentAlreadyChecked) {
        $consentBlocked = unipixel_check_for_consent();
        if ($consentBlocked) {
            return $consentBlocked;
        }
    }

    $platformId       = 2; // Pinterest
    $platformSettings = unipixel_get_platform_settings($platformId);

    $ad_account_id = $platformSettings->additional_id ?? '';
    $access_token  = $platformSettings->access_token ?? '';

    if (empty($ad_account_id) || empty($access_token)) {
        return [
            'response' => ['error' => 'Missing Pinterest Ad Account ID or Access Token'],
            'args'     => [],
            'payload'  => [],
        ];
    }

    // Normalize event time to Unix seconds
    $eventTime = !empty($eventTime_in) ? (int) $eventTime_in : time();
    if ($eventTime > 9999999999) {
        $eventTime = (int) floor($eventTime / 1000);
    }

    // Build user_data for Pinterest — values arrive already hashed from prepare functions
    $pinterest_user_data = [
        'client_ip_address' => $user_data['client_ip_address'] ?? '',
        'client_user_agent' => $user_data['client_user_agent'] ?? '',
    ];

    // Pass through already-hashed PII fields (array format for Pinterest)
    $pinterest_passthrough_fields = ['em', 'ph', 'fn', 'ln', 'ct', 'st', 'zp', 'country', 'click_id', 'external_id'];
    foreach ($pinterest_passthrough_fields as $field) {
        if (!empty($user_data[$field])) {
            $pinterest_user_data[$field] = $user_data[$field];
        }
    }

    // Build event payload
    $eventInfoPayloadArr = [
        'event_name'       => $event_name,
        'action_source'    => 'web',
        'event_time'       => $eventTime,
        'event_id'         => $event_id,
        'event_source_url' => $pageUrl ?: unipixel_get_current_page_url(),
        'user_data'        => $pinterest_user_data,
    ];

    if (!empty($custom_data) && is_array($custom_data)) {
        $eventInfoPayloadArr['custom_data'] = $custom_data;
    }

    $body = [
        'data' => [$eventInfoPayloadArr],
    ];

    // Request array for wp_remote_post()
    $wpRemotePostSendArr = [
        'body'    => wp_json_encode($body),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
        'method'  => 'POST',
    ];

    $endpoint = "https://api.pinterest.com/v5/ad_accounts/{$ad_account_id}/events";

    // Initialize stub result
    $wpRemotePostResult = [
        'response' => [
            'code'    => 0,
            'message' => '',
        ],
    ];

    // Execute send
    if ($sendServerLogResponse) {
        $wpRemotePostSendArr['timeout'] = 2.5;
        $wpRemotePostResult = wp_remote_post($endpoint, $wpRemotePostSendArr);
    } else {
        $wpRemotePostSendArr['timeout']  = 0.1;
        $wpRemotePostSendArr['blocking'] = false;
        wp_remote_post($endpoint, $wpRemotePostSendArr);
    }

    // Unified structure, consistent across all platforms
    $sendServerResultInfo = [
        'response' => $wpRemotePostResult,
        'args'     => $wpRemotePostSendArr,
        'payload'  => $eventInfoPayloadArr,
    ];

    return $sendServerResultInfo;
}



function unipixel_send_server_event_tiktok(
    $event_name,
    $user_data,
    $custom_data,
    $event_id,
    $eventTime_in = null,
    $pageUrl = '',
    $sendServerLogResponse = false,
    $consentAlreadyChecked = false
) {
    if (!$consentAlreadyChecked) {
        $consentBlocked = unipixel_check_for_consent();
        if ($consentBlocked) {
            return $consentBlocked;
        }
    }

    $platformId       = 3; // TikTok
    $platformSettings = unipixel_get_platform_settings($platformId);

    $pixel_id     = $platformSettings->pixel_id ?? '';
    $access_token = $platformSettings->access_token ?? '';

    if (empty($pixel_id) || empty($access_token)) {
        return [
            'response' => ['error' => 'Missing TikTok Pixel ID or Access Token'],
            'args'     => [],
            'payload'  => [],
        ];
    }

    // Normalize event time to seconds (TikTok requires 10-digit timestamp)
    if (!empty($eventTime_in)) {
        $eventTime = (int)$eventTime_in;
        if ($eventTime > 9999999999) { // Likely milliseconds
            $eventTime = (int) floor($eventTime / 1000);
        }
    } else {
        $eventTime = time();
    }

    // Logical event data
    $eventInfoPayloadArr = [
        'event_source'    => 'web',
        'event_source_id' => $pixel_id,
        'data'            => [[
            'event'      => $event_name,
            'event_id'   => $event_id,
            'event_time' => $eventTime,
            'context'    => [
                'user' => $user_data,
                'page' => [
                    'url' => $pageUrl ?: unipixel_get_current_page_url(),
                ],
            ],
        ]],
    ];

    if (!empty($custom_data) && is_array($custom_data)) {
        $eventInfoPayloadArr['data'][0]['properties'] = $custom_data;
    }

    // Request array for wp_remote_post()
    $wpRemotePostSendArr = [
        'body'    => wp_json_encode($eventInfoPayloadArr),
        'headers' => [
            'Content-Type' => 'application/json',
            'Access-Token' => $access_token,
        ],
        'method'  => 'POST',
    ];

    $endpoint = 'https://business-api.tiktok.com/open_api/v1.3/event/track/';

    // Initialize stub result
    $wpRemotePostResult = [
        'response' => [
            'code'    => 0,
            'message' => '',
        ],
    ];

    // Execute send
    if ($sendServerLogResponse) {
        $wpRemotePostSendArr['timeout'] = 2.5;
        $wpRemotePostResult = wp_remote_post($endpoint, $wpRemotePostSendArr);
    } else {
        $wpRemotePostSendArr['timeout']  = 0.1;
        $wpRemotePostSendArr['blocking'] = false;
        wp_remote_post($endpoint, $wpRemotePostSendArr);
    }

    // Unified structure, consistent across all platforms
    $sendServerResultInfo = [
        'response' => $wpRemotePostResult,
        'args'     => $wpRemotePostSendArr,
        'payload'  => $eventInfoPayloadArr,
    ];

    return $sendServerResultInfo;
}



function unipixel_send_server_event_microsoft(
    $event_name,
    $user_data,
    $custom_data,
    $event_id,
    $eventTime_in = null,
    $pageUrl = '',
    $sendServerLogResponse = false,
    $consentAlreadyChecked = false
) {
    if (!$consentAlreadyChecked) {
        $consentBlocked = unipixel_check_for_consent();
        if ($consentBlocked) {
            return $consentBlocked;
        }
    }

    $platformId       = 5; // Microsoft
    $platformSettings = unipixel_get_platform_settings($platformId);

    $tag_id       = $platformSettings->pixel_id ?? '';
    $access_token = $platformSettings->access_token ?? '';

    if (empty($tag_id) || empty($access_token)) {
        return [
            'response' => ['error' => 'Missing Microsoft UET Tag ID or Access Token'],
            'args'     => [],
            'payload'  => [],
        ];
    }

    // Normalize event time to Unix seconds (10-digit)
    if (!empty($eventTime_in)) {
        $eventTime = (int) $eventTime_in;
        if ($eventTime > 9999999999) {
            $eventTime = (int) floor($eventTime / 1000);
        }
    } else {
        $eventTime = time();
    }

    // Determine eventType: pageLoad for PageView, custom for everything else
    $isPageLoad = ($event_name === 'PageView' || $event_name === 'pageLoad');
    $eventType = $isPageLoad ? 'pageLoad' : 'custom';

    // Build Microsoft CAPI userData — pass through supported fields
    $microsoft_user_data = [];
    $ms_user_fields = ['clientUserAgent', 'clientIpAddress', 'msclkid', 'em', 'ph', 'anonymousId', 'externalId'];
    foreach ($ms_user_fields as $field) {
        if (!empty($user_data[$field])) {
            $microsoft_user_data[$field] = $user_data[$field];
        }
    }

    // Build event payload
    $eventInfoPayloadArr = [
        'eventType'      => $eventType,
        'eventId'        => $event_id,
        'eventTime'      => $eventTime,
        'eventSourceUrl' => $pageUrl ?: unipixel_get_current_page_url(),
    ];

    if ($eventType === 'custom') {
        $eventInfoPayloadArr['eventName'] = $event_name;
    }

    // Consent signal — if we reach here, consent was already granted or not required
    $eventInfoPayloadArr['adStorageConsent'] = 'G';

    if (!empty($microsoft_user_data)) {
        $eventInfoPayloadArr['userData'] = $microsoft_user_data;
    }

    if (!empty($custom_data) && is_array($custom_data)) {
        $eventInfoPayloadArr['customData'] = $custom_data;
    }

    $body = [
        'data' => [$eventInfoPayloadArr],
    ];

    // Request array for wp_remote_post()
    $wpRemotePostSendArr = [
        'body'    => wp_json_encode($body),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
        'method'  => 'POST',
    ];

    $endpoint = "https://capi.uet.microsoft.com/v1/{$tag_id}/events";

    // Initialize stub result
    $wpRemotePostResult = [
        'response' => [
            'code'    => 0,
            'message' => '',
        ],
    ];

    // Execute send
    if ($sendServerLogResponse) {
        $wpRemotePostSendArr['timeout'] = 2.5;
        $wpRemotePostResult = wp_remote_post($endpoint, $wpRemotePostSendArr);
    } else {
        $wpRemotePostSendArr['timeout']  = 0.1;
        $wpRemotePostSendArr['blocking'] = false;
        wp_remote_post($endpoint, $wpRemotePostSendArr);
    }

    // Unified structure, consistent across all platforms
    $sendServerResultInfo = [
        'response' => $wpRemotePostResult,
        'args'     => $wpRemotePostSendArr,
        'payload'  => $eventInfoPayloadArr,
    ];

    return $sendServerResultInfo;
}
