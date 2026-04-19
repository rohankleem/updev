// File: public_html\wp-content\plugins\unipixel\js\pixel-meta.js

//create unipixel fbc cookie if it doesn't already exist, if not created server side

(function() {
    // Toggle this to switch attribution model:
    // true  = last-click overwrite
    // false = first-click only
    var useLastClickAttr = true;

    var urlParams = new URLSearchParams(window.location.search);
    var fbclid    = urlParams.get('fbclid');
    if (!fbclid) {
        return;
    }

    var cookieName = 'unipixel_fbclid';
    var cookieExists = document.cookie.split('; ').some(function(c) {
        return c.indexOf(cookieName + '=') === 0;
    });

    if (useLastClickAttr) {
        // always overwrite with the newest fbclid
        document.cookie = cookieName + '=' + encodeURIComponent(fbclid) +
                          '; path=/; max-age=' + (60*60*24*90);
    } else if (!cookieExists) {
        // only set once (first-click)
        document.cookie = cookieName + '=' + encodeURIComponent(fbclid) +
                          '; path=/; max-age=' + (60*60*24*90);
    }
})();

! function(f, b, e, v, n, t, s) {
    if (f.fbq) return;
    n = f.fbq = function() {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
    };
    if (!f._fbq) f._fbq = n;
    n.push = n;
    n.loaded = !0;
    n.version = '2.0';
    n.queue = [];
    t = b.createElement(e);
    t.async = !0;
    t.src = v;
    s = b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t, s)
}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

// Init with Advanced Matching data if available
var metaAmData = (metaPixelSettings.advanced_matching && Object.keys(metaPixelSettings.advanced_matching).length > 0)
    ? metaPixelSettings.advanced_matching
    : {};
fbq('init', metaPixelSettings.pixel_id, metaAmData);
//fbq('track', 'PageView'); //handled by UniPixel
