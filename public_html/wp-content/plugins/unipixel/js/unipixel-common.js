
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

