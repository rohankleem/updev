
//File: public_html\wp-content\plugins\unipixel\js\unipixel-common.js

// This script ensures that UniPixelOrderData is available for use by other scripts
if (typeof UniPixelOrderData === 'undefined') {
    var UniPixelOrderData = {};
}


/**
 * Run a callback once the DOM is ready. Unlike a bare DOMContentLoaded
 * listener, this still fires when the script executes after the event
 * (e.g. under defer/delay JS optimisers), matching jQuery.ready semantics.
 */
window.unipixelOnReady = function (fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
};

/**
 * POST form-encoded data and resolve with the parsed JSON response.
 * Encodes nested objects/arrays in PHP bracket notation exactly as
 * jQuery.param() did, so admin-ajax handlers see an identical $_POST.
 * Rejects on network failure or a non-JSON response body.
 */
window.unipixelAjaxPost = function (url, data) {
    var body = new URLSearchParams();
    (function build(prefix, value) {
        if (value === null || value === undefined) {
            body.append(prefix, '');
        } else if (Array.isArray(value)) {
            value.forEach(function (item, i) {
                if (item !== null && typeof item === 'object') {
                    build(prefix + '[' + i + ']', item);
                } else {
                    build(prefix + '[]', item);
                }
            });
        } else if (typeof value === 'object') {
            Object.keys(value).forEach(function (key) {
                build(prefix === '' ? key : prefix + '[' + key + ']', value[key]);
            });
        } else {
            body.append(prefix, value);
        }
    })('', data);

    return fetch(url, { method: 'POST', credentials: 'same-origin', body: body })
        .then(function (response) { return response.text(); })
        .then(function (text) { return JSON.parse(text); });
};

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

    // Fire-and-forget: a failed log write must never break tracking.
    return unipixelAjaxPost(UniPixelAjax.ajaxurl, payload)
        .catch(function () {});
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

