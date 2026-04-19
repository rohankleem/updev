//File: public_html/wp-content/plugins/unipixel/js/pixel-tiktok.js

// Create unipixel_ttclid cookie if not already set (used for server-side matching)
(function () {
    // true = overwrite each time (last-click model)
    // false = set once (first-click model)
    var useLastClickAttr = true;

    var urlParams = new URLSearchParams(window.location.search);
    var ttclid = urlParams.get('ttclid');
    if (!ttclid) {
        return;
    }

    var cookieName = 'unipixel_ttclid';
    var cookieExists = document.cookie.split('; ').some(function (c) {
        return c.indexOf(cookieName + '=') === 0;
    });

    if (useLastClickAttr || !cookieExists) {
        document.cookie =
            cookieName + '=' + encodeURIComponent(ttclid) +
            '; path=/; max-age=' + (60 * 60 * 24 * 90);
    }
})();

// TikTok Pixel base code (no consent check, no PageView)
!function (w, d, t) {
    w.TiktokAnalyticsObject = t;
    var ttq = w[t] = w[t] || [];
    ttq.methods = [
        "page", "track", "identify", "instances", "debug", "on", "off", "once",
        "ready", "alias", "group", "enableCookie", "disableCookie",
        "holdConsent", "revokeConsent", "grantConsent"
    ];
    ttq.setAndDefer = function (t, e) {
        t[e] = function () {
            t.push([e].concat(Array.prototype.slice.call(arguments, 0)));
        };
    };
    for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
    ttq.instance = function (t) {
        var e = ttq._i[t] || [];
        for (var n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]);
        return e;
    };
    ttq.load = function (e, n) {
        var r = "https://analytics.tiktok.com/i18n/pixel/events.js";
        ttq._i = ttq._i || {};
        ttq._i[e] = [];
        ttq._i[e]._u = r;
        ttq._t = ttq._t || {};
        ttq._t[e] = +new Date;
        ttq._o = ttq._o || {};
        ttq._o[e] = n || {};
        var s = d.createElement("script");
        s.type = "text/javascript";
        s.async = true;
        s.src = r + "?sdkid=" + e + "&lib=" + t;
        var f = d.getElementsByTagName("script")[0];
        f.parentNode.insertBefore(s, f);
    };

    // Initialise TikTok Pixel using localized ID
    ttq.load(tiktokPixelSettings.pixel_id);

    // Identify user with Advanced Matching data if available
    if (tiktokPixelSettings.advanced_matching && Object.keys(tiktokPixelSettings.advanced_matching).length > 0) {
        ttq.identify(tiktokPixelSettings.advanced_matching);
    }

    // ttq.page(); // handled by UniPixel, do not auto-fire
}(window, document, 'ttq');
