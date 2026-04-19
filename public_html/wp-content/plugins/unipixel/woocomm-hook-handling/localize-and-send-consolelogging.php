<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\localize-and-send-consolelogging.php

/**
 * Localize + inline console logging for WooCommerce-triggered server events.
 * Schema-agnostic: dump event + payload only.
 */

function unipixel_localize_console_logging_for_meta($eventName, array $sendServerResultInfo) {
    $scriptData = [
        'event_name' => $eventName,
        'payload'    => $sendServerResultInfo['payload']  ?? [],
        'response'   => $sendServerResultInfo['response'] ?? [],
    ];
    wp_localize_script('unipixel-common', 'UniPixelConsoleLogMeta', $scriptData);

    $script = "
        if (typeof UniPixelConsoleLogMeta !== 'undefined' && window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Meta | Server-side event sent | ' + UniPixelConsoleLogMeta.event_name + ' object:', UniPixelConsoleLogMeta.payload);
            if (UniPixelConsoleLogMeta.response && Object.keys(UniPixelConsoleLogMeta.response).length > 0) {
                UniPixelConsoleLogger.log('RESPONSE', 'Meta | Platform response | ' + UniPixelConsoleLogMeta.event_name + ' object:', UniPixelConsoleLogMeta.response);
            }
        }
    ";
    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_localize_console_logging_for_google($eventName, array $sendServerResultInfo) {
    $scriptData = [
        'event_name' => $eventName,
        'payload'    => $sendServerResultInfo['payload']  ?? [],
        'response'   => $sendServerResultInfo['response'] ?? [],
    ];
    wp_localize_script('unipixel-common', 'UniPixelConsoleLogGoogle', $scriptData);

    $script = "
        if (typeof UniPixelConsoleLogGoogle !== 'undefined' && window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Google | Server-side event sent | ' + UniPixelConsoleLogGoogle.event_name + ' object:', UniPixelConsoleLogGoogle.payload);
            if (UniPixelConsoleLogGoogle.response && Object.keys(UniPixelConsoleLogGoogle.response).length > 0) {
                UniPixelConsoleLogger.log('RESPONSE', 'Google | Platform response | ' + UniPixelConsoleLogGoogle.event_name + ' object:', UniPixelConsoleLogGoogle.response);
            }
        }
    ";
    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_localize_console_logging_for_tiktok($eventName, array $sendServerResultInfo) {
    $scriptData = [
        'event_name' => $eventName,
        'payload'    => $sendServerResultInfo['payload']  ?? [],
        'response'   => $sendServerResultInfo['response'] ?? [],
    ];
    wp_localize_script('unipixel-common', 'UniPixelConsoleLogTiktok', $scriptData);

    $script = "
        if (typeof UniPixelConsoleLogTiktok !== 'undefined' && window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'TikTok | Server-side event sent | ' + UniPixelConsoleLogTiktok.event_name + ' object:', UniPixelConsoleLogTiktok.payload);
            if (UniPixelConsoleLogTiktok.response && Object.keys(UniPixelConsoleLogTiktok.response).length > 0) {
                UniPixelConsoleLogger.log('RESPONSE', 'TikTok | Platform response | ' + UniPixelConsoleLogTiktok.event_name + ' object:', UniPixelConsoleLogTiktok.response);
            }
        }
    ";
    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_localize_console_logging_for_pinterest($eventName, array $sendServerResultInfo) {
    $scriptData = [
        'event_name' => $eventName,
        'payload'    => $sendServerResultInfo['payload']  ?? [],
        'response'   => $sendServerResultInfo['response'] ?? [],
    ];
    wp_localize_script('unipixel-common', 'UniPixelConsoleLogPinterest', $scriptData);

    $script = "
        if (typeof UniPixelConsoleLogPinterest !== 'undefined' && window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Pinterest | Server-side event sent | ' + UniPixelConsoleLogPinterest.event_name + ' object:', UniPixelConsoleLogPinterest.payload);
            if (UniPixelConsoleLogPinterest.response && Object.keys(UniPixelConsoleLogPinterest.response).length > 0) {
                UniPixelConsoleLogger.log('RESPONSE', 'Pinterest | Platform response | ' + UniPixelConsoleLogPinterest.event_name + ' object:', UniPixelConsoleLogPinterest.response);
            }
        }
    ";
    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_localize_console_logging_for_microsoft($eventName, array $sendServerResultInfo) {
    $scriptData = [
        'event_name' => $eventName,
        'payload'    => $sendServerResultInfo['payload']  ?? [],
        'response'   => $sendServerResultInfo['response'] ?? [],
    ];
    wp_localize_script('unipixel-common', 'UniPixelConsoleLogMicrosoft', $scriptData);

    $script = "
        if (typeof UniPixelConsoleLogMicrosoft !== 'undefined' && window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Microsoft | Server-side event sent | ' + UniPixelConsoleLogMicrosoft.event_name + ' object:', UniPixelConsoleLogMicrosoft.payload);
            if (UniPixelConsoleLogMicrosoft.response && Object.keys(UniPixelConsoleLogMicrosoft.response).length > 0) {
                UniPixelConsoleLogger.log('RESPONSE', 'Microsoft | Platform response | ' + UniPixelConsoleLogMicrosoft.event_name + ' object:', UniPixelConsoleLogMicrosoft.response);
            }
        }
    ";
    wp_add_inline_script('unipixel-common', $script, 'after');
}

