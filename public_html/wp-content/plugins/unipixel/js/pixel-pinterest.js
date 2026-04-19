// File: public_html/wp-content/plugins/unipixel/js/pixel-pinterest.js

// Capture epik click ID from URL and store in cookie (used for server-side matching)
(function () {
    // true = overwrite each time (last-click model)
    // false = set once (first-click model)
    var useLastClickAttr = true;

    var urlParams = new URLSearchParams(window.location.search);
    var epik = urlParams.get('epik');
    if (!epik) {
        return;
    }

    var cookieName = 'unipixel_epik';
    var cookieExists = document.cookie.split('; ').some(function (c) {
        return c.indexOf(cookieName + '=') === 0;
    });

    if (useLastClickAttr || !cookieExists) {
        document.cookie =
            cookieName + '=' + encodeURIComponent(epik) +
            '; path=/; max-age=' + (60 * 60 * 24 * 90);
    }
})();

// Pinterest Tag base code
!function (e) {
    if (!window.pintrk) {
        window.pintrk = function () {
            window.pintrk.queue.push(Array.prototype.slice.call(arguments));
        };
        var n = window.pintrk;
        n.queue = [];
        n.version = "3.0";
        var t = document.createElement("script");
        t.async = true;
        t.src = "https://s.pinimg.com/ct/core.js";
        var r = document.getElementsByTagName("script")[0];
        r.parentNode.insertBefore(t, r);
    }
}(window);

// Initialise Pinterest Tag using localized ID
pintrk('load', pinterestPixelSettings.pixel_id);

// Set Advanced Matching data if available (Pinterest supports em via pintrk('set'))
if (pinterestPixelSettings.advanced_matching && pinterestPixelSettings.advanced_matching.em) {
    pintrk('set', { em: pinterestPixelSettings.advanced_matching.em });
}

// pintrk('page'); // handled by UniPixel, do not auto-fire
