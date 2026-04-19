// File: public_html\wp-content\plugins\unipixel\js\clientfirst-watch-and-send-google-helper.js

(function (global) {
    'use strict';

    function readCookie(name) {
        const match = document.cookie.match(new RegExp(
            '(?:^|; )' + name.replace(/([.*+?^${}()|[\]\\])/g, '\\$1') + '=([^;]+)'
        ));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function parseClientIdFromGaCookie(value) {
        if (!value) return '';
        // already "X.Y"?
        if (/^\d+\.\d+$/.test(value)) return value;
        // strip GAx.y. prefix e.g. GA1.1.123456.1700000000 -> 123456.1700000000
        const m = value.match(/^GA\d+\.\d+\.(\d+\.\d+)$/);
        return m ? m[1] : '';
    }

    function getCachedClientId() {
        try { return sessionStorage.getItem('unipixel_google_client_id') || ''; } catch { return ''; }
    }
    function cacheClientId(clientId) {
        try { if (clientId) sessionStorage.setItem('unipixel_google_client_id', clientId); } catch { }
    }

    /**
     * Resolve the canonical GA4 client_id with a short retry.
     * Order: cache -> gtag getter -> _ga cookie parse -> ''.
     */
    function resolveGoogleClientId(options) {
        const {
            measurementId,
            maxAttempts = 3,
            attemptDelayMs = 150
        } = options || {};

        return new Promise((resolve) => {
            const cached = getCachedClientId();
            if (cached) return resolve(cached);

            let attempts = 0;
            const tryOnce = () => {
                attempts += 1;
                const canUseGtag = typeof window.gtag === 'function' && !!measurementId;

                if (canUseGtag) {
                    try {
                        window.gtag('get', measurementId, 'client_id', function (raw) {
                            const normalized = parseClientIdFromGaCookie(raw || '');
                            if (normalized) { cacheClientId(normalized); return resolve(normalized); }
                            const fromCookie = parseClientIdFromGaCookie(readCookie('_ga'));
                            if (fromCookie) { cacheClientId(fromCookie); return resolve(fromCookie); }
                            if (attempts < maxAttempts) return setTimeout(tryOnce, attemptDelayMs);
                            return resolve('');
                        });
                        return; // async branch
                    } catch (_) { /* fall through */ }
                }

                const fromCookie = parseClientIdFromGaCookie(readCookie('_ga'));
                if (fromCookie) { cacheClientId(fromCookie); return resolve(fromCookie); }

                if (attempts < maxAttempts) return setTimeout(tryOnce, attemptDelayMs);
                return resolve('');
            };

            tryOnce();
        });
    }

    function getGclid() {
        const m = document.cookie.match(/(?:^|; )unipixel_gclid=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function ensureDataLayer() {
        if (!Array.isArray(global.dataLayer)) { global.dataLayer = []; }
        return global.dataLayer;
    }

    // Public API
    global.GoogleHelper = {
        resolveGoogleClientId,
        parseClientIdFromGaCookie,
        getGclid,
        ensureDataLayer
    };
})(window);
