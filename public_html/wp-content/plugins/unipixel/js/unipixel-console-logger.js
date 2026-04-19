//File: public_html\wp-content\plugins\unipixel\js\unipixel-console-logger.js

(function () {
    // Only clear logs on frontend load if admin console is actively open in another tab
    if (localStorage.getItem('__unipixel_console_active') === '1') {
        localStorage.removeItem('unipixel_live_log');

        // Optional: Add a friendly message so admin can see it cleared
        localStorage.setItem('unipixel_live_log', JSON.stringify([{
            ts: new Date().toISOString(),
            category: 'SYSTEM',
            message: 'Logs reset on frontend page load',
            data: null
        }]));
    }
})();


(function (window) {
    'use strict';

    // Config
    const LOG_STORAGE_KEY = 'unipixel_live_log';
    const MAX_LOGS = 200;

    // Global logging state
    if (localStorage.getItem('__unipixel_console_active') === '1') {
        window.__UniPixelConsoleActive = true;
        window.UniPixelConsoleState = {
            logSendEvents: true,
            logInitiationEvents: localStorage.getItem('unipixel_log_initiate') === '1'
        };
    }

    // Internal buffer (mirror of localStorage)
    function getStoredLogs() {
        try {
            return JSON.parse(localStorage.getItem(LOG_STORAGE_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function saveLogEntry(entry) {
        const logs = getStoredLogs();
        logs.push(entry);
        if (logs.length > MAX_LOGS) logs.shift();
        localStorage.setItem(LOG_STORAGE_KEY, JSON.stringify(logs));
    }

    function nowTimestamp() {
        const d = new Date();
        return d.toISOString();
    }

    const UniPixelConsoleLogger = {
        log: function (category, message, data) {
            // 1. Console
            // if (typeof console !== 'undefined' && console.log) {
            //     if (typeof data !== 'undefined') console.log(message, data);
            //     else console.log(message);
            // }

            // 2. Log object
            const entry = {
                ts: nowTimestamp(),
                category: category || 'LOG',
                message: message,
                data: data || null
            };

            saveLogEntry(entry);
        },

        error: function (message, data) {
            if (typeof console !== 'undefined' && console.error) {
                if (typeof data !== 'undefined') console.error(message, data);
                else console.error(message);
            }

            const entry = {
                ts: nowTimestamp(),
                category: 'ERROR',
                message: message,
                data: data || null
            };

            saveLogEntry(entry);
        },

        clear: function () {
            localStorage.removeItem(LOG_STORAGE_KEY);
        }
    };

    window.UniPixelConsoleLogger = UniPixelConsoleLogger;

})(window);