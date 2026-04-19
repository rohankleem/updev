// File: admin/js/unipixel-console-log-admin.js
(function ($) {
    'use strict';

    const LOG_STORAGE_KEY = 'unipixel_live_log';



    //window.__UniPixelConsoleActive = true;
    localStorage.setItem('__unipixel_console_active', '1');

    window.addEventListener('beforeunload', function () {
        localStorage.removeItem('__unipixel_console_active');
    });

    var rawInitiateSetting = localStorage.getItem('unipixel_log_initiate');
    var logInitiation = false;
    if (rawInitiateSetting !== null && rawInitiateSetting === '1') {
        logInitiation = true;
    }

    window.UniPixelConsoleState = {
        logSendEvents: true,             // Console view is open → enable logging
        logInitiationEvents: logInitiation
    };


    // Init toggle from localStorage
    $(document).ready(function () {
        var rawSetting = localStorage.getItem('unipixel_log_initiate');
        var isEnabled = (rawSetting === '1');
        var $toggle = $('#unipixel-initiate-toggle');

        if ($toggle.length > 0) {
            $toggle.prop('checked', isEnabled);

            $toggle.on('change', function () {
                if ($toggle.is(':checked')) {
                    localStorage.setItem('unipixel_log_initiate', '1');
                } else {
                    localStorage.setItem('unipixel_log_initiate', '0');
                }

                // Reload to reinit logger state
                location.reload();
            });
        }
    });



    function renderLogEntry(entry) {
        const ts = entry.ts || '[no timestamp]';
        const cat = entry.category || 'LOG';
        const msg = entry.message || '[no message]';
        const data = entry.data ? JSON.stringify(entry.data, null, 2) : '';

        let html = '<div style="margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #ccc;">';
        html += '<div><strong>' + ts + ' [' + cat + ']</strong></div>';
        html += '<div>' + msg + '</div>';
        if (data) {
            html += '<pre style="margin-top:5px; background:#f0f0f0; padding:5px; white-space:pre-wrap; word-break:break-word;">' + data + '</pre>';
        }
        html += '</div>';

        return html;
    }

    function loadAndRenderLogs() {
        let logs = [];
        try {
            logs = JSON.parse(localStorage.getItem(LOG_STORAGE_KEY)) || [];
        } catch (e) {
            logs = [];
        }

        const container = $('#unipixel-console-log');
        container.empty();

        if (logs.length === 0) {
            container.html('No logs yet - interact with the front end of your website to trigger the events that are setup.');
            return;
        }

        logs.forEach(function (entry) {
            container.append(renderLogEntry(entry));
        });

        // Optional: auto-scroll to bottom
        container.scrollTop(container.prop('scrollHeight'));
    }

    $(document).ready(function () {
        loadAndRenderLogs();

        // Refresh every 2 seconds
        setInterval(loadAndRenderLogs, 2000);

        // Bind clear button
        $('#unipixel-clear-log').on('click', function (e) {
            e.preventDefault();
            localStorage.removeItem(LOG_STORAGE_KEY);
            loadAndRenderLogs();

        });
    });

})(jQuery);
