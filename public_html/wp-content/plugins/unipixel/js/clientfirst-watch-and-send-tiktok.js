//File: public_html\wp-content\plugins\unipixel\js\clientfirst-watch-and-send-tiktok.js

(function ($) {
    $(document).ready(function () {

        // --- Retrieve logging settings ---
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

        // --- Initialization ---
        log_Initiate('UniPixel | Initiate | TikTok | UniPixelEventDataTikTok Obj:', UniPixelEventDataTikTok);
        log_Initiate('UniPixel | Initiate | TikTok | Tracker Loaded');

        // --- Event trigger handler ---
        function clientFirstEventTriggered_TikTok(event, element) {
            log_Send('UniPixel | TikTok | Event triggered from client-side, preparing event-send:', event.name);

            if (!window.unipixelCheckConsentForEvent()) {
                log_Send('UniPixel | Consent Result | TikTok | Consent not granted, blocking event:', event.name);
                return;
            }

            var event_id = 'event_' + new Date().getTime();
            var event_params = {};

            event.send_client = event.send_client ?? 0;
            event.send_server = event.send_server ?? 0;
            event.send_server_log_response = event.send_server_log_response ?? 0;

            // --- CLIENT-SIDE SEND ---
            if (event.send_client === 1) {

                if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {

                    ttq.track(event.name, event_params, { event_id: event_id });

                    log_Send('UniPixel | TikTok | Client-side event sent:', {
                        event_name: event.name,
                        event_params: event_params,
                        event_id: event_id
                    });

                    unipixelLogClientEvent({
                        platform_id: 3, // TikTok
                        element_ref: event.elementRef,
                        event_trigger: event.trigger,
                        event_name: event.name,
                        json_data_sent: {
                            event_name: event.name,
                            event_params: event_params,
                            //event_id: event_id // in params
                        }
                    });
                } else {
                    log_Send('UniPixel | TikTok | Client-side pixel not available: ttq not found', event.name);
                }
            } else {
                log_Send('UniPixel | TikTok | Client-side send not enabled:', event.name);
            }

            // --- SERVER-SIDE SEND ---
            if (event.send_server === 1) {
                if (!UniPixelEventDataTikTok.serverside_global_enabled) {
                    log_Send('UniPixel | TikTok | Server-side not sent (off at platform level):', event.name);
                } else {
                const ajaxPayload = {
                    action: 'ajax_data_for_server_event_tiktok',
                    eventName: event.name,
                    elementRef: event.elementRef,
                    eventTrigger: event.trigger,
                    event_id: event_id,
                    pageUrl: window.location.href,
                    nonce: UniPixelEventDataTikTok.nonce
                };

                $.post(UniPixelEventDataTikTok.ajaxurl, ajaxPayload)
                    .done(function (resp) {
                        var jsn = (typeof resp === "string") ? JSON.parse(resp) : resp;

                        if (!jsn.dataSent) {
                            log_Send('UniPixel | TikTok | Server-side event not sent:', jsn);
                            return;
                        }

                        log_Send('UniPixel | TikTok | Server-side event sent:', jsn.dataSent);

                        if (event.send_server_log_response) {
                            log_Send('UniPixel | TikTok | Server-side platform response enabled. Response:', jsn.platformResponse);
                        } else {
                            log_Send('UniPixel | TikTok | Server-side platform response disabled, not waiting for response');
                        }
                    })
                    .fail(function (_, status, err) {
                        log_Send('UniPixel | TikTok | Server-side event error:', status, err);
                    });
                }
            } else {
                log_Send('UniPixel | TikTok | Server-side not sent (off for this event):', event.name);
            }
        }

        // --- Attach triggers (click / shown) ---
        UniPixelEventDataTikTok.eventsToTrack.forEach(function (event) {
            log_Initiate('UniPixel | TikTok | Setting up tracking for event:', event);

            document.querySelectorAll(event.elementRef).forEach(element => {
                if (event.trigger === "click") {
                    element.addEventListener('click', function () {
                        clientFirstEventTriggered_TikTok(event, element);
                    });
                }

                if (event.trigger === "shown") {
                    var shownTriggered = false;

                    // Intersection observer
                    var intersectionObserver = new IntersectionObserver(function (entries, observer) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting && !shownTriggered) {
                                clientFirstEventTriggered_TikTok(event, element);
                                shownTriggered = true;
                                observer.disconnect();
                            }
                        });
                    });
                    intersectionObserver.observe(element);

                    // Mutation observer (handles late DOM changes)
                    var mutationObserver = new MutationObserver(function (mutations) {
                        mutations.forEach(function () {
                            if (element.offsetParent !== null && !shownTriggered) {
                                clientFirstEventTriggered_TikTok(event, element);
                                shownTriggered = true;
                                mutationObserver.disconnect();
                            }
                        });
                    });
                    mutationObserver.observe(element, { attributes: true, childList: false, subtree: false });
                }
            });
        });
    });
})(jQuery);
