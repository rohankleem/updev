
//File: public_html\wp-content\plugins\unipixel\js\unipixel-common.js

// This script ensures that UniPixelOrderData is available for use by other scripts
if (typeof UniPixelOrderData === 'undefined') {
    var UniPixelOrderData = {};
}


window.UniPixelGetCookieValue = function (cookieName) {
    const name = cookieName + "=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};


/**
 * Log a client-side event to the server.
 * Shared across all platforms (Meta, Google, etc)
 */
function unipixelLogClientEvent(options) {
    if (typeof UniPixelAjax === 'undefined') {
        //console.warn('UniPixelAjax is not defined. Cannot send log.');
        return;
    }

    const payload = {
        action: 'unipixel_log_client_event',
        nonce: UniPixelAjax.nonce,
        platform_id: options.platform_id,
        element_ref: options.element_ref,
        event_trigger: options.event_trigger,
        event_name: options.event_name,
        response_log_message: options.response_log_message || '',
        json_data_sent: JSON.stringify(options.json_data_sent || {}),
        party: options.party || 'third',
        event_order: options.event_order || 'clientFirst'
    };

    return jQuery.post(UniPixelAjax.ajaxurl, payload)
        .done(function(response) {
            if (response.success) {
                //console.log('UniPixel | Logger | Log sent successfully:', response);
            } else {
                //console.warn('UniPixel | Logger | Log failed:', response);
            }
        })
        .fail(function(_, status, err) {
            //console.error('UniPixel | Logger | Log error:', status, err);
        });
}

// Mirrors unipixel_url_pattern_match in unipixel-functions.php — must stay identical.
window.unipixelMatchUrlPattern = function (pattern, url) {
    if (typeof pattern !== 'string' || typeof url !== 'string') return false;
    pattern = pattern.trim();
    if (pattern === '') return false;

    var origin = (typeof window !== 'undefined' && window.location) ? window.location.origin : 'http://localhost';
    var urlObj;
    try {
        urlObj = new URL(url, origin);
    } catch (e) {
        return false;
    }
    var urlPath  = urlObj.pathname;
    var urlQuery = urlObj.search ? urlObj.search.replace(/^\?/, '') : '';

    var target = (pattern.indexOf('?') === -1)
        ? urlPath
        : urlPath + (urlQuery !== '' ? '?' + urlQuery : '');

    target  = unipixelStripTrailingSlashFromPath(target);
    pattern = unipixelStripTrailingSlashFromPath(pattern);

    var targetLc  = target.toLowerCase();
    var patternLc = pattern.toLowerCase();

    var escaped = patternLc
        .replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
        .replace(/\\\*/g, '.*');
    var regex = new RegExp('^' + escaped + '$');

    return regex.test(targetLc);
};

function unipixelStripTrailingSlashFromPath(s) {
    var qpos = s.indexOf('?');
    if (qpos === -1) {
        var stripped = s.replace(/\/+$/, '');
        return stripped === '' ? '/' : stripped;
    }
    var path = s.substring(0, qpos);
    var rest = s.substring(qpos);
    var pathStripped = path.replace(/\/+$/, '');
    if (pathStripped === '') pathStripped = '/';
    return pathStripped + rest;
}

// Fire-once-per-session guard for url-trigger events. Returns true on first call
// for a given (platform, eventName, pattern) within the session; false thereafter.
// Returns false (without marking fired) if consent is currently denied — so that a
// later consent grant can still fire the event.
// Falls open (returns true) if sessionStorage is unavailable.
window.unipixelShouldFireUrlEvent = function (platformName, eventName, pattern) {
    if (typeof window.unipixelCheckConsentForEvent === 'function' && !window.unipixelCheckConsentForEvent()) {
        return false;
    }
    var key = 'unipixel_url_fired:' + platformName + ':' + eventName + ':' + pattern;
    try {
        if (sessionStorage.getItem(key) === '1') return false;
        sessionStorage.setItem(key, '1');
        return true;
    } catch (e) {
        return true;
    }
};

