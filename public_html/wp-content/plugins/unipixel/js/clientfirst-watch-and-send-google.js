// File: public_html\wp-content\plugins\unipixel\js\clientfirst-watch-and-send-google.js

(function ($) {
    $(document).ready(function () {


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


        var enableGoogleDebugViewClientSide = (typeof UniPixelSettings !== 'undefined' && typeof UniPixelSettings.enableGoogleDebugViewClientSide !== 'undefined')
            ? UniPixelSettings.enableGoogleDebugViewClientSide : true;

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


        // Log UniPixelEventDataGoogle to check its contents
        log_Initiate('UniPixel | Initiate | Google | UniPixelEventDataGoogle Obj:', UniPixelEventDataGoogle);
        log_Initiate('UniPixel | Initiate | Google | Tracker Loaded');

        // Start client-first event track
        function clientFirstEventTriggered_Google(event, element) {

            log_Send('UniPixel | Google | Event triggered from client-side, preparing event-send:', event.name);

            if (!window.unipixelCheckConsentForEvent()) {
                log_Send('UniPixel | Consent Result | Google | Consent not granted, blocking event:', event.name);
                return;
            }

            //log_Send('UniPixel | Google | tracking event:', { event, element });
            var eventId = 'event_' + new Date().getTime(); //generated new everytime a track event is triggered 
            var eventParams = {};

            event.send_client = event.send_client ?? 0;
            event.send_server = event.send_server ?? 0;
            event.send_server_log_response = event.send_server_log_response ?? 0;

            if (event.name === 'page_view') {
                eventParams.page_location = window.location.href;
                eventParams.engagement_time_msec = 1000;
            }

            clientFirstSendEvent_Google(
                event.name,
                eventParams,
                eventId,
                event.elementRef,
                event.trigger,
                event.send_client,
                event.send_server,
                event.send_server_log_response
            );

        }


        // Function to send event data to Google Analytics
        function clientFirstSendEvent_Google(
            eventName,
            eventParams,
            eventId,
            elementRef,
            eventTrigger,
            sendClient,
            sendServer,
            sendServerLogResponse
        ) {

            const gclid = GoogleHelper.getGclid();

            //flatten all params into one array
            const clientSideParams = {
                ...eventParams,
                event_id: eventId,
                event_send_method: 'clientFirst',
                ...(enableGoogleDebugViewClientSide ? { debug_mode: true } : {}),
                ...(gclid ? { gclid } : {})     // only add if non‐empty
            };

            // Log the pixel setting to verify the correct flow
            log_Send('UniPixel | Google | Pixel Setting:', UniPixelEventDataGoogle.pixel_setting);

            //The following are several of the parameters that are collected by default with every event, including custom events:
            //• language
            //• page_location
            //• page_referrer
            //• page_title
            //• screen_resolution”
            // Under the hood, GA4 will enrich that hit with the above five parameters plus:
            // Client ID (from the _ga cookie)
            // Device & browser info (OS, screen size, device category)
            // Geo (country/region inferred from IP)
            // Session identifiers (ga_session_id, ga_session_number)
            // Language (navigator.language)


            // NOTE on client_id:
            // --------------------------------------------------------------
            // client_id is not sent in client-side params.
            // GA4 automatically attaches it from the _ga cookie.
            // For server-side hits, client_id must be resolved
            // and included explicitly for deduplication.

            // Client-side event handling
            if (sendClient) {
                if (UniPixelEventDataGoogle.pixel_setting === 'include' || UniPixelEventDataGoogle.pixel_setting === 'already_included') {
                    gtag('event', eventName, clientSideParams);
                    log_Send('UniPixel | Google | Client-side gtag event sent:', { eventName, clientSideParams });
                } else if (UniPixelEventDataGoogle.pixel_setting === 'gtm') {

                    // Ensure dataLayer is initialized
                    if (typeof window.dataLayer === 'undefined') {
                        window.dataLayer = [];
                    }

                    window.dataLayer.push(
                        Object.assign({ event: eventName }, clientSideParams)
                    );

                    log_Send('UniPixel | Google | Client-side dataLayer event sent:', { eventName, clientSideParams });
                }

                unipixelLogClientEvent({
                    platform_id: 4, // Google
                    element_ref: elementRef,
                    event_trigger: eventTrigger,
                    event_name: eventName,
                    json_data_sent: {
                        event_name: eventName,
                        event_params: clientSideParams,
                        event_id: eventId
                    }
                });


            } else {
                log_Send('UniPixel | Google | Client-side send not enabled: client-side event not sent:', { eventName, clientSideParams });
            }


            // Server-side event handling (resolve canonical GA4 client_id first)
            const isGtm = (UniPixelEventDataGoogle && UniPixelEventDataGoogle.pixel_setting === 'gtm');

            if (sendServer) {
                if (!UniPixelEventDataGoogle.serverside_global_enabled) {
                    log_Send('UniPixel | Google | Server-side not sent (off at platform level):', eventName);
                } else {
                GoogleHelper.resolveGoogleClientId({
                    measurementId: (typeof UniPixelEventDataGoogle !== 'undefined' ? UniPixelEventDataGoogle.measurement_id : ''),
                    maxAttempts: isGtm ? 6 : 3,
                    attemptDelayMs: isGtm ? 200 : 150
                }).then(function (googleClientId) {

                    const ajaxPayload = {
                        action: 'ajax_data_for_server_event_google',
                        nonce: UniPixelEventDataGoogle.nonce,
                        eventName: eventName,
                        event_id: eventId,
                        eventParams: eventParams,
                        elementRef: elementRef,
                        eventTrigger: eventTrigger,
                        googleClientId: googleClientId     // ← normalized "X.Y" (or '')
                    };


                    $.post(UniPixelEventDataGoogle.ajaxurl, ajaxPayload)

                        .done(function (resp) {

                            var jsn = (typeof resp === "string") ? JSON.parse(resp) : resp;

                            if (!jsn.dataSent) {
                                log_Send('UniPixel | Google | Server-side event not sent:', jsn);
                                return;
                            }

                            log_Send('UniPixel | Google | Server-side event sent:', jsn.dataSent);

                            if (sendServerLogResponse) {
                                log_Send('UniPixel | Google | Server-side platform response enabled. Response:', jsn.platformResponse);
                            } else {
                                log_Send('UniPixel | Google | Server-side platform response disabled, not waiting for response');
                            }

                        })
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            log_Send('UniPixel | Google | Server-side event error:', { textStatus, errorThrown, responseText: jqXHR.responseText });
                        });
                });
                }
            } else {
                log_Send('UniPixel | Google | Server-side not sent (off for this event):', eventName);
            }

        }




        // Iterate over the events to track
        UniPixelEventDataGoogle.eventsToTrack.forEach(function (event) {
            log_Initiate('UniPixel | Initiate | Google | Setting up tracking for event:', event);
            document.querySelectorAll(event.elementRef).forEach(element => {
                if (event.trigger === "click") {
                    //log_Initiate('Adding click event listener for:', event.elementRef);
                    element.addEventListener('click', function () {
                        clientFirstEventTriggered_Google(event, element);
                    });
                }

                if (event.trigger === "shown") {
                    // IntersectionObserver instance
                    var shownTriggered = false;
                    var intersectionObserver = new IntersectionObserver(function (entries, observer) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting && !shownTriggered) {
                                clientFirstEventTriggered_Google(event, element);
                                shownTriggered = true;
                                observer.disconnect(); // Properly scoped observer
                            }
                        });
                    });
                    intersectionObserver.observe(element);

                    // MutationObserver instance
                    var mutationObserver = new MutationObserver(function (mutations) {
                        mutations.forEach(function (mutation) {
                            if (element.offsetParent !== null && !shownTriggered) {
                                clientFirstEventTriggered_Google(event, element);
                                shownTriggered = true;
                                mutationObserver.disconnect(); // Properly scoped observer
                            }
                        });
                    });
                    mutationObserver.observe(element, { attributes: true, childList: false, subtree: false });
                }
            });
        });
    });
})(jQuery);
