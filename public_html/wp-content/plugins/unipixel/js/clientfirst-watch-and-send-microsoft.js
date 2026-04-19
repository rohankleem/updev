//File: public_html\wp-content\plugins\unipixel\js\clientfirst-watch-and-send-microsoft.js

document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // Logging state initialization
    // ===========================
    var enableLogging_SendEvents = false;
    if (typeof UniPixelConsoleState !== 'undefined' && UniPixelConsoleState.logSendEvents === true) {
        enableLogging_SendEvents = true;
    }
    var enableLogging_InitiateEvents = false;
    if (typeof UniPixelConsoleState !== 'undefined' && UniPixelConsoleState.logInitiationEvents === true) {
        enableLogging_InitiateEvents = true;
    }

    function log_Initiate(message, data) {
        if (enableLogging_InitiateEvents && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('INITIATE', message, data);
        }
    }

    function log_Send(message, data) {
        if (enableLogging_SendEvents && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', message, data);
        }
    }

    // ===========================
    // Initialization + diagnostics
    // ===========================
    var eventsToTrack = UniPixelEventDataMicrosoft.eventsToTrack || [];

    log_Initiate('UniPixel | Microsoft | UniPixelEventDataMicrosoft Obj:', UniPixelEventDataMicrosoft);
    log_Initiate('UniPixel | Microsoft | Tracker Loaded');

    // ===========================
    // Core event trigger handler
    // ===========================
    function clientFirstEventTriggered_Microsoft(event, element) {

        log_Send('UniPixel | Microsoft | Event triggered:', event.name);

        // Consent gate (unified)
        if (typeof window.unipixelCheckConsentForEvent === 'function' && !window.unipixelCheckConsentForEvent()) {
            log_Send('UniPixel | Consent Result | Microsoft | Consent not granted, blocking event:', event.name);
            return;
        }

        var event_id = 'event_' + new Date().getTime();
        var event_params = {};

        // Normalize flags
        event.send_client = event.send_client ?? 0;
        event.send_server = event.send_server ?? 0;
        event.send_server_log_response = event.send_server_log_response ?? 0;

        // ===================================
        // Client-side send (Microsoft UET Tag)
        // ===================================
        if (event.send_client === 1) {

            var parameters = {
                'event_label': event.name,
                'event_id': event_id
            };

            log_Send('UniPixel | Microsoft | Client-side event send:', { event_name: event.name, parameters: parameters });

            // Fire to Microsoft Ads UET
            window.uetq = window.uetq || [];
            window.uetq.push('event', event.name, parameters);

            // Unified local DB logger
            if (typeof unipixelLogClientEvent === 'function') {
                unipixelLogClientEvent({
                    platform_id: 5, // Microsoft
                    element_ref: event.elementRef,
                    event_trigger: event.trigger,
                    event_name: event.name,
                    json_data_sent: {
                        event_name: event.name,
                        parameters: parameters,
                        event_id: event_id
                    }
                });
            }

        } else {
            log_Send('UniPixel | Microsoft | Client-side send not enabled for event:', event.name);
        }

        // ===================================
        // Server-side send (Microsoft CAPI)
        // ===================================
        if (event.send_server === 1) {
            if (!UniPixelEventDataMicrosoft.serverside_global_enabled) {
                log_Send('UniPixel | Microsoft | Server-side not sent (off at platform level):', event.name);
            } else {

                var ajaxPayload = {
                    action: 'unipixel_ajax_server_event_microsoft',
                    eventName: event.name,
                    elementRef: event.elementRef,
                    eventTrigger: event.trigger,
                    event_id: event_id,
                    pageUrl: window.location.href,
                    nonce: UniPixelEventDataMicrosoft.nonce
                };

                jQuery.post(UniPixelEventDataMicrosoft.ajaxurl, ajaxPayload)
                    .done(function (resp) {
                        log_Send('UniPixel | Microsoft | Server-side response:', resp);
                    })
                    .fail(function (_, status, err) {
                        log_Send('UniPixel | Microsoft | Server-side AJAX error:', { status: status, error: err });
                    });
            }
        } else {
            log_Send('UniPixel | Microsoft | Server-side not sent (off for this event):', event.name);
        }
    }

    // ===========================
    // Attach triggers
    // ===========================
    eventsToTrack.forEach(function (event) {
        log_Initiate('UniPixel | Microsoft | Setting up tracking for:', event);

        document.querySelectorAll(event.elementRef).forEach(element => {

            if (event.trigger === "click") {
                element.addEventListener('click', function () {
                    clientFirstEventTriggered_Microsoft(event, element);
                });
            }

            if (event.trigger === "shown") {
                var shownTriggered = false;
                new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && !shownTriggered) {
                            clientFirstEventTriggered_Microsoft(event, element);
                            shownTriggered = true;
                            observer.disconnect();
                        }
                    });
                }).observe(element);

                var mutationObserver = new MutationObserver(function (mutations) {
                    mutations.forEach(function () {
                        if (element.offsetParent !== null && !shownTriggered) {
                            clientFirstEventTriggered_Microsoft(event, element);
                            shownTriggered = true;
                            mutationObserver.disconnect();
                        }
                    });
                });

                mutationObserver.observe(element, { attributes: true });
            }
        });
    });
});
