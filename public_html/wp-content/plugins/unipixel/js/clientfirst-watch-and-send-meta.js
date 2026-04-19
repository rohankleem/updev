
// File: public_html\wp-content\plugins\unipixel\js\clientfirst-watch-and-send-meta.js

document.addEventListener('DOMContentLoaded', function () {

    //console.warn('Logger check at load:', typeof UniPixelConsoleLogger);

    // Retrieve logging settings from the localized object, if available.
    // Fallback to true if the settings are not defined.
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

    var eventsToTrack = UniPixelEventDataMeta.eventsToTrack;
    var standardEvents = [
        "AddPaymentInfo", "AddToCart", "AddToWishlist", "CompleteRegistration",
        "Contact", "CustomizeProduct", "Donate", "FindLocation",
        "InitiateCheckout", "Lead", "PageView", "Purchase",
        "Schedule", "Search", "StartTrial", "SubmitApplication",
        "Subscribe", "ViewContent"
    ];

    log_Initiate('UniPixel | Initiate | Meta | UniPixelEventDataMeta Obj:', UniPixelEventDataMeta);
    log_Initiate('UniPixel | Initiate | Meta | Tracker Loaded');

    function clientFirstEventTriggered_Meta(event, element) {

        log_Send('UniPixel | Meta | Event triggered from client-side, preparing event-send:', event.name);

        if (!window.unipixelCheckConsentForEvent()) {
            log_Send('UniPixel | Consent Result | Meta | Consent not granted, blocking event:', event.name);
            return;
        }

        var event_id = 'event_' + new Date().getTime(); //generated new everytime a track event is triggered 
        var event_params = {};
        var isStandard = standardEvents.includes(event.name);

        event.send_client = event.send_client ?? 0;
        event.send_server = event.send_server ?? 0;
        event.send_server_log_response = event.send_server_log_response ?? 0;

        // determine fbq method and label
        var fbqMethod = isStandard ? 'track' : 'trackCustom';
        var fbqLabel = isStandard ? 'standard' : 'custom';

        // fire the fbq call
        // NOTE: Meta Pixel automatically captures the page URL (event_source_url), user agent, IP, cookies, and action_source
        // this client-side code only needs to supply the eventID (and any explicit event_params) for deduplication.

        if (event.send_client === 1) {
            fbq(fbqMethod, event.name, event_params, { eventID: event_id });

            log_Send('UniPixel | Meta | Client-side event sent:', { event_name: event.name, event_params: event_params, event_id: event_id });

            unipixelLogClientEvent({
                platform_id: 1, // Meta
                element_ref: event.elementRef,
                event_trigger: event.trigger,
                event_name: event.name,
                json_data_sent: {
                    event_name: event.name,
                    event_params: event_params,
                    event_id: event_id
                }
            });

        } else {
            log_Send('UniPixel | Meta | Client-side send not enabled: client-side event not sent:', { event_name: event.name, event_params: event_params, event_id: event_id });
        }



        if (event.send_server === 1) {
            if (!UniPixelEventDataMeta.serverside_global_enabled) {
                log_Send('UniPixel | Meta | Server-side not sent (off at platform level):', event.name);
            } else {
            // server-side AJAX

            const ajaxPayload = {
                action: 'ajax_data_for_server_event_meta',
                eventName: event.name,
                elementRef: event.elementRef,
                eventTrigger: event.trigger,
                event_id: event_id,
                pageUrl: window.location.href,
                nonce: UniPixelEventDataMeta.nonce
            };

            jQuery.post(UniPixelEventDataMeta.ajaxurl, ajaxPayload)

                .done(function (resp) {
                    var jsn = (typeof resp === "string") ? JSON.parse(resp) : resp;

                    if (!jsn.dataSent) {
                        log_Send('UniPixel | Meta | Server-side event not sent:', jsn);
                        return;
                    }

                    log_Send('UniPixel | Meta | Server-side event sent:', jsn.dataSent);

                    if (event.send_server_log_response) {
                        log_Send('UniPixel | Meta | Server-side platform response enabled. Response:', jsn.platformResponse);
                    } else {
                        log_Send('UniPixel | Meta | Server-side platform response disabled, not waiting for response');
                    }
                })
                .fail(function (_, status, err) {
                    log_Send('UniPixel | Meta | Server-side event error:', status, err);
                });
            }
        } else {
            log_Send('UniPixel | Meta | Server-side not sent (off for this event):', event.name);
        }
    }


    eventsToTrack.forEach(function (event) {
        log_Initiate('UniPixel | Meta | Setting up tracking for event:', event);

        document.querySelectorAll(event.elementRef).forEach(element => {
            if (event.trigger === "click") {
                element.addEventListener('click', function () {
                    clientFirstEventTriggered_Meta(event, element);
                });
            }

            if (event.trigger === "shown") {
                var shownTriggered = false;
                new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && !shownTriggered) {
                            clientFirstEventTriggered_Meta(event, element);
                            shownTriggered = true;
                            observer.disconnect();
                        }
                    });
                }).observe(element);

                var mutationObserver = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (element.offsetParent !== null && !shownTriggered) {
                            clientFirstEventTriggered_Meta(event, element);
                            shownTriggered = true;
                            mutationObserver.disconnect(); // Use the correct reference
                        }
                    });
                });

                mutationObserver.observe(element, { attributes: true, childList: false, subtree: false });
            }
        });
    });
});
