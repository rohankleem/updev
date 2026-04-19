<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-send-checkout.php

function unipixel_inline_script_meta_initiate_checkout()
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! is_checkout() || is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(1, 'InitiateCheckout')) {
        return;
    }

    $script = "
        if (typeof UniPixelInitiateCheckoutMeta !== 'undefined' && typeof fbq !== 'undefined') {
            var payload = {
                currency: UniPixelInitiateCheckoutMeta.currency,
                value: UniPixelInitiateCheckoutMeta.value,
                tax: UniPixelInitiateCheckoutMeta.tax,
                shipping: UniPixelInitiateCheckoutMeta.shipping,
                contents: UniPixelInitiateCheckoutMeta.contents,
                content_type: UniPixelInitiateCheckoutMeta.content_type,
                content_ids: UniPixelInitiateCheckoutMeta.content_ids
            };

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | InitiateCheckout object:', UniPixelInitiateCheckoutMeta);
                // UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | InitiateCheckout data sent:', payload);
            }

            fbq('track','InitiateCheckout', payload, { eventID: UniPixelInitiateCheckoutMeta.event_id });
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Meta | InitiateCheckout payload not sent: UniPixelInitiateCheckoutMeta or fbq is undefined');
            }
        }
        ";


    wp_add_inline_script('unipixel-common', $script, 'after');
}

function unipixel_inline_script_google_initiate_checkout()
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! is_checkout() || is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(4, 'begin_checkout')) {
        return;
    }

    $opts = get_option('unipixel_logging_options', []);
    $enableGoogleDebugViewClientSide = ! empty($opts['enableGoogleDebugViewClientSide']);

    $googleDebugViewJs = $enableGoogleDebugViewClientSide ? "payload.debug_mode = true;" : "";

    $script = "
        if (typeof UniPixelInitiateCheckoutGoogle !== 'undefined') {
            var payload = {
                event_id: UniPixelInitiateCheckoutGoogle.event_id,
                currency: UniPixelInitiateCheckoutGoogle.currency,
                value: UniPixelInitiateCheckoutGoogle.value,
                tax: UniPixelInitiateCheckoutGoogle.tax,
                shipping: UniPixelInitiateCheckoutGoogle.shipping,
                items: UniPixelInitiateCheckoutGoogle.items,
                event_send_method: 'clientSecond'
            };

            if (UniPixelInitiateCheckoutGoogle.gclid) {
                payload.gclid = UniPixelInitiateCheckoutGoogle.gclid;
            }

            {$googleDebugViewJs}

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | InitiateCheckout object:', UniPixelInitiateCheckoutGoogle);
                // UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | InitiateCheckout data sent:', payload);
            }

            if (typeof gtag !== 'undefined') {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.log('SEND', 'Google InitiateCheckout: using gtag() branch');
                }
                gtag('event', 'begin_checkout', payload);
            } else if (typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)) {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.log('SEND', 'Google InitiateCheckout: using dataLayer.push branch');
                }
                window.dataLayer.push(
                    Object.assign({ event: 'begin_checkout' }, payload)
                );
            } else {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.error('Google InitiateCheckout: no tracking method detected');
                }
            }
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Google | InitiateCheckout payload not sent: UniPixelInitiateCheckoutGoogle is undefined');
            }
        }
        ";


    wp_add_inline_script('unipixel-common', $script, 'after');
}



function unipixel_inline_script_tiktok_initiate_checkout()
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    // Only run on checkout page, and not on the order-received page
    if (! is_checkout() || is_order_received_page()) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(3, 'InitiateCheckout')) {
        return;
    }

$script = "
    if (typeof UniPixelInitiateCheckoutTikTok !== 'undefined' && typeof ttq !== 'undefined') {
        var payload = {
            contents: (UniPixelInitiateCheckoutTikTok.line_items || []).map(function(item) {
                return {
                    content_id:   item.content_id,
                    content_name: item.content_name,
                    content_type: item.content_type,
                    quantity:     item.quantity,
                    price:        item.price
                };
            }),
            currency: UniPixelInitiateCheckoutTikTok.currency,
            value:    UniPixelInitiateCheckoutTikTok.value,
            tax:      UniPixelInitiateCheckoutTikTok.tax,
            shipping: UniPixelInitiateCheckoutTikTok.shipping
        };

        if (UniPixelInitiateCheckoutTikTok.ttclid) {
            payload.ttclid = UniPixelInitiateCheckoutTikTok.ttclid;
        }
        if (UniPixelInitiateCheckoutTikTok.ttp) {
            payload.ttp = UniPixelInitiateCheckoutTikTok.ttp;
        }

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'TikTok | Client-side event sent | InitiateCheckout object:', UniPixelInitiateCheckoutTikTok);
        }

        ttq.track('InitiateCheckout', payload, { event_id: UniPixelInitiateCheckoutTikTok.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('TikTok | InitiateCheckout payload not sent: UniPixelInitiateCheckoutTikTok or ttq is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_pinterest_initiate_checkout()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! is_checkout() || is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(2, 'initiate_checkout')) {
        return;
    }

    $script = "
    if (typeof UniPixelInitiateCheckoutPinterest !== 'undefined' && typeof pintrk === 'function') {
        var payload = {
            value:       UniPixelInitiateCheckoutPinterest.value,
            currency:    UniPixelInitiateCheckoutPinterest.currency,
            num_items:   UniPixelInitiateCheckoutPinterest.num_items,
            content_ids: UniPixelInitiateCheckoutPinterest.content_ids,
            contents:    UniPixelInitiateCheckoutPinterest.contents
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Pinterest | Client-side event sent | InitiateCheckout object:', UniPixelInitiateCheckoutPinterest);
        }

        pintrk('track', 'initiatecheckout', payload, { event_id: UniPixelInitiateCheckoutPinterest.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Pinterest | InitiateCheckout payload not sent: UniPixelInitiateCheckoutPinterest or pintrk is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_microsoft_checkout()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! is_checkout() || is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(5, 'begin_checkout')) {
        return;
    }

    $script = "
    if (typeof UniPixelCheckoutMicrosoft !== 'undefined') {
        window.uetq = window.uetq || [];

        var payload = {
            event_id:        UniPixelCheckoutMicrosoft.event_id,
            currency:        UniPixelCheckoutMicrosoft.currency,
            ecomm_totalvalue: UniPixelCheckoutMicrosoft.value,
            ecomm_pagetype:  'checkout',
            items:           UniPixelCheckoutMicrosoft.items
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Microsoft | Client-side event sent | InitiateCheckout object:', UniPixelCheckoutMicrosoft);
        }

        window.uetq.push('event', 'begin_checkout', payload);
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Microsoft | InitiateCheckout payload not sent: UniPixelCheckoutMicrosoft is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}

