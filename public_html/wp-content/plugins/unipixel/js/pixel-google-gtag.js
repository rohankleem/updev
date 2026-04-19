// File: public_html\wp-content\plugins\unipixel\js\pixel-google-gtag.js

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



// pixel-google-gtag.js
window.dataLayer = window.dataLayer || [];

// if Google’s library didn’t already set window.gtag,
// define it here so the rest of code can call it.
window.gtag = window.gtag || function(){
  dataLayer.push(arguments);
};

// now initialize
gtag('js', new Date());
gtag('config', googleGtagSettings.pixel_id, {
  send_page_view: false
});
