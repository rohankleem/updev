
// File: public_html\wp-content\plugins\unipixel\js\clientfirst-watch-and-send-pinterest.js

document.addEventListener('DOMContentLoaded', function () {

    // Retrieve logging settings from the localized object, if available.
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

    var eventsToTrack = UniPixelEventDataPinterest.eventsToTrack;

    // Pinterest client-side event name mapping (server uses snake_case, client uses lowercase)
    var pinterestClientEventMap = {
        'PageView':         'pagevisit',
        'page_visit':       'pagevisit',
        'add_to_cart':      'addtocart',
        'checkout':         'checkout',
        'initiate_checkout':'initiatecheckout',
        'view_content':     'viewcontent',
        'view_category':    'viewcategory',
        'search':           'search',
        'lead':             'lead',
        'signup':           'signup',
        'watch_video':      'watchvideo',
        'custom':           'custom'
    };

    log_Initiate('UniPixel | Initiate | Pinterest | UniPixelEventDataPinterest Obj:', UniPixelEventDataPinterest);
    log_Initiate('UniPixel | Initiate | Pinterest | Tracker Loaded');

    function clientFirstEventTriggered_Pinterest(event, element) {

        log_Send('UniPixel | Pinterest | Event triggered from client-side, preparing event-send:', event.name);

        if (!window.unipixelCheckConsentForEvent()) {
            log_Send('UniPixel | Consent Result | Pinterest | Consent not granted, blocking event:', event.name);
            return;
        }

        var event_id = 'event_' + new Date().getTime();
        var event_params = {};

        event.send_client = event.send_client ?? 0;
        event.send_server = event.send_server ?? 0;
        event.send_server_log_response = event.send_server_log_response ?? 0;

        // Map event name to Pinterest client format
        var pinEventName = pinterestClientEventMap[event.name] || event.name.toLowerCase();

        if (event.send_client === 1) {
            if (typeof pintrk === 'function') {
                pintrk('track', pinEventName, event_params, { event_id: event_id });
            } else if (window.pintrk) {
                pintrk('track', pinEventName, event_params, { event_id: event_id });
            }

            log_Send('UniPixel | Pinterest | Client-side event sent:', { event_name: pinEventName, event_params: event_params, event_id: event_id });

            unipixelLogClientEvent({
                platform_id: 2, // Pinterest
                element_ref: event.elementRef,
                event_trigger: event.trigger,
                event_name: pinEventName,
                json_data_sent: {
                    event_name: pinEventName,
                    event_params: event_params,
                    event_id: event_id
                }
            });

        } else {
            log_Send('UniPixel | Pinterest | Client-side send not enabled: client-side event not sent:', { event_name: pinEventName, event_params: event_params, event_id: event_id });
        }


        if (event.send_server === 1) {
            if (!UniPixelEventDataPinterest.serverside_global_enabled) {
                log_Send('UniPixel | Pinterest | Server-side not sent (off at platform level):', event.name);
            } else {
            // server-side AJAX

            const ajaxPayload = {
                action: 'ajax_data_for_server_event_pinterest',
                eventName: event.name,
                elementRef: event.elementRef,
                eventTrigger: event.trigger,
                event_id: event_id,
                pageUrl: window.location.href,
                nonce: UniPixelEventDataPinterest.nonce
            };

            jQuery.post(UniPixelEventDataPinterest.ajaxurl, ajaxPayload)

                .done(function (resp) {
                    var jsn = (typeof resp === "string") ? JSON.parse(resp) : resp;

                    if (!jsn.dataSent) {
                        log_Send('UniPixel | Pinterest | Server-side event not sent:', jsn);
                        return;
                    }

                    log_Send('UniPixel | Pinterest | Server-side event sent:', jsn.dataSent);

                    if (event.send_server_log_response) {
                        log_Send('UniPixel | Pinterest | Server-side platform response enabled. Response:', jsn.platformResponse);
                    } else {
                        log_Send('UniPixel | Pinterest | Server-side platform response disabled, not waiting for response');
                    }
                })
                .fail(function (_, status, err) {
                    log_Send('UniPixel | Pinterest | Server-side event error:', status, err);
                });
            }
        } else {
            log_Send('UniPixel | Pinterest | Server-side not sent (off for this event):', event.name);
        }
    }


    eventsToTrack.forEach(function (event) {
        log_Initiate('UniPixel | Pinterest | Setting up tracking for event:', event);

        if (event.trigger === "url") {
            if (typeof window.unipixelMatchUrlPattern === 'function' &&
                window.unipixelMatchUrlPattern(event.elementRef, window.location.href) &&
                window.unipixelShouldFireUrlEvent('pinterest', event.name, event.elementRef)) {
                clientFirstEventTriggered_Pinterest(event, null);
            }
            return;
        }

        document.querySelectorAll(event.elementRef).forEach(element => {
            if (event.trigger === "click") {
                element.addEventListener('click', function () {
                    clientFirstEventTriggered_Pinterest(event, element);
                });
            }

            if (event.trigger === "shown") {
                var shownTriggered = false;
                new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && !shownTriggered) {
                            clientFirstEventTriggered_Pinterest(event, element);
                            shownTriggered = true;
                            observer.disconnect();
                        }
                    });
                }).observe(element);

                var mutationObserver = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (element.offsetParent !== null && !shownTriggered) {
                            clientFirstEventTriggered_Pinterest(event, element);
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
