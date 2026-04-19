//File: public_html\wp-content\plugins\unipixel\js\pixel-microsoft.js

// Microsoft UET consent signal — must be pushed before the tag loads
(function() {
    window.uetq = window.uetq || [];
    var consentState = 'granted'; // default if consent honour is off
    try {
        var match = document.cookie.match(/(?:^|; )unipixel_consent_summary=([^;]*)/);
        if (match) {
            var summary = JSON.parse(decodeURIComponent(match[1]));
            if (summary.marketing === false) {
                consentState = 'denied';
            }
        }
    } catch (e) { /* silently default to granted */ }
    window.uetq.push('consent', 'ad_storage', consentState);
})();

(function(w,d,t,r,u) {
    var f,n,i;
    w[u]=w[u]||[],f=function() {
        var o={ti: microsoftPixelSettings.pixel_id, enableAutoSpaTracking: true};
        o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")
    },
    n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function() {
        var s=this.readyState;
        s&&s!=='loaded'&&s!=='complete'||(f(),n.onload=n.onreadystatechange=null)
    },
    i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)
})(window,document,"script","//bat.bing.com/bat.js","uetq");
