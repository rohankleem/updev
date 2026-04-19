// File: public_html\wp-content\plugins\unipixel\js\pixel-google-gtm.js

(function() {
    // Toggle attribution model:
    // true  = last-click overwrite
    // false = first-click only
    var useLastClickAttr = true;

    var urlParams = new URLSearchParams(window.location.search);
    var gclid = urlParams.get('gclid');
    if (!gclid) {
        return;
    }

    var cookieName   = 'unipixel_gclid';
    var cookieExists = document.cookie.split('; ').some(function(c) {
        return c.indexOf(cookieName + '=') === 0;
    });

    if (useLastClickAttr) {
        // always overwrite with the newest gclid
        document.cookie = cookieName + '=' + encodeURIComponent(gclid) +
                          '; path=/; max-age=' + (60*60*24*90);
    } else if (!cookieExists) {
        // only set once (first-click)
        document.cookie = cookieName + '=' + encodeURIComponent(gclid) +
                          '; path=/; max-age=' + (60*60*24*90);
    }
})();



// pixel-google-gtm.js
(function(w, d, s, l, i) {
    w[l] = w[l] || [];
    w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
    });
    var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
    j.async = true;
    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + googleGtmSettings.additional_id + dl;
    f.parentNode.insertBefore(j, f);
})(window, document, 'script', 'dataLayer', googleGtmSettings.additional_id);
