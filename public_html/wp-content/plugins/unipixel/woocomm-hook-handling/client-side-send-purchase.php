<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-send-purchase.php

function unipixel_inline_script_meta_purchase()
{
    if (! class_exists('WooCommerce')) {
        return;
    }
    if (! is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(1, 'Purchase')) {
        return;
    }

    $script = "
        if (typeof UniPixelPurchaseMeta !== 'undefined' && typeof fbq === 'function') {

            var payload = {
                contents:       UniPixelPurchaseMeta.contents,
                content_type:   UniPixelPurchaseMeta.content_type,
                content_ids:    UniPixelPurchaseMeta.content_ids,
                currency:       UniPixelPurchaseMeta.currency,
                value:          UniPixelPurchaseMeta.value,
                transaction_id: UniPixelPurchaseMeta.transaction_id,
                tax:            UniPixelPurchaseMeta.tax,
                shipping:       UniPixelPurchaseMeta.shipping
            };

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | Purchase object:', UniPixelPurchaseMeta);
                // UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | Purchase data sent:', payload);
            }

            fbq('track', 'Purchase', payload, { eventID: UniPixelPurchaseMeta.event_id });
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Meta | Purchase payload not sent: UniPixelPurchaseMeta or fbq is undefined');
            }
        }
        ";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_google_purchase()
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(4, 'purchase')) {
        return;
    }

    $opts = get_option('unipixel_logging_options', []);
    $enableGoogleDebugViewClientSide = ! empty($opts['enableGoogleDebugViewClientSide']);

    $googleDebugViewJs = $enableGoogleDebugViewClientSide ? "payload.debug_mode = true;" : "";

    $script = "
        if (typeof UniPixelPurchaseGoogle !== 'undefined') {
            var payload = {
                event_id: UniPixelPurchaseGoogle.event_id,
                currency: UniPixelPurchaseGoogle.currency,
                value: UniPixelPurchaseGoogle.value,
                transaction_id: UniPixelPurchaseGoogle.transaction_id,
                tax: UniPixelPurchaseGoogle.tax,
                shipping: UniPixelPurchaseGoogle.shipping,
                items: UniPixelPurchaseGoogle.items,
                event_send_method: 'clientSecond'
            };

            if (UniPixelPurchaseGoogle.gclid) {
                payload.gclid = UniPixelPurchaseGoogle.gclid;
            }

            {$googleDebugViewJs}

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | Purchase object:', UniPixelPurchaseGoogle);
                // UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | Purchase data sent:', payload);
            }

            if (typeof gtag !== 'undefined') {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.log('SEND', 'Google Purchase: using gtag() branch');
                }
                gtag('event', 'purchase', payload);
            } else if (typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)) {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.log('SEND', 'Google Purchase: using dataLayer.push branch');
                }
                window.dataLayer.push(
                    Object.assign({ event: 'purchase' }, payload)
                );
            } else {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.error('Google Purchase: no tracking method detected');
                }
            }
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Google | Purchase payload not sent: UniPixelPurchaseGoogle is undefined');
            }
        }
        ";

    wp_add_inline_script('unipixel-common', $script, 'after');
}



function unipixel_inline_script_tiktok_purchase()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    // Fire only on the order confirmation / thank-you page
    if (! is_order_received_page()) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(3, 'Purchase')) {
        return;
    }

    $script = "
    if (typeof UniPixelPurchaseTikTok !== 'undefined' && typeof ttq !== 'undefined') {
        var payload = {
            contents: (UniPixelPurchaseTikTok.line_items || []).map(function(item) {
                return {
                    content_id:   item.content_id,
                    content_name: item.content_name,
                    content_type: item.content_type,
                    quantity:     item.quantity,
                    price:        item.price
                };
            }),
            currency:       UniPixelPurchaseTikTok.currency,
            value:          UniPixelPurchaseTikTok.value,
            transaction_id: UniPixelPurchaseTikTok.transaction_id,
            tax:            UniPixelPurchaseTikTok.tax,
            shipping:       UniPixelPurchaseTikTok.shipping
        };

        if (UniPixelPurchaseTikTok.ttclid) {
            payload.ttclid = UniPixelPurchaseTikTok.ttclid;
        }
        if (UniPixelPurchaseTikTok.ttp) {
            payload.ttp = UniPixelPurchaseTikTok.ttp;
        }

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'TikTok | Client-side event sent | Purchase object:', UniPixelPurchaseTikTok);
        }

        ttq.track('Purchase', payload, { event_id: UniPixelPurchaseTikTok.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('TikTok | Purchase payload not sent: UniPixelPurchaseTikTok or ttq is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_pinterest_purchase()
{
    if (! class_exists('WooCommerce')) {
        return;
    }
    if (! is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(2, 'checkout')) {
        return;
    }

    $script = "
    if (typeof UniPixelPurchasePinterest !== 'undefined' && typeof pintrk === 'function') {
        var payload = {
            value:       UniPixelPurchasePinterest.value,
            currency:    UniPixelPurchasePinterest.currency,
            order_id:    UniPixelPurchasePinterest.order_id,
            num_items:   UniPixelPurchasePinterest.num_items,
            content_ids: UniPixelPurchasePinterest.content_ids,
            contents:    UniPixelPurchasePinterest.contents
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Pinterest | Client-side event sent | Purchase (checkout) object:', UniPixelPurchasePinterest);
        }

        pintrk('track', 'checkout', payload, { event_id: UniPixelPurchasePinterest.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Pinterest | Purchase payload not sent: UniPixelPurchasePinterest or pintrk is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_microsoft_purchase()
{
    if (! class_exists('WooCommerce')) {
        return;
    }
    if (! is_order_received_page()) {
        return;
    }
    if (! unipixel_is_woo_event_enabled(5, 'purchase')) {
        return;
    }

    $script = "
    if (typeof UniPixelPurchaseMicrosoft !== 'undefined') {
        window.uetq = window.uetq || [];

        var payload = {
            event_id:        UniPixelPurchaseMicrosoft.event_id,
            revenue:         UniPixelPurchaseMicrosoft.value,
            currency:        UniPixelPurchaseMicrosoft.currency,
            transaction_id:  UniPixelPurchaseMicrosoft.transaction_id,
            ecomm_totalvalue: UniPixelPurchaseMicrosoft.value,
            ecomm_pagetype:  'purchase',
            items:           UniPixelPurchaseMicrosoft.items
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Microsoft | Client-side event sent | Purchase object:', UniPixelPurchaseMicrosoft);
        }

        window.uetq.push('event', 'purchase', payload);
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Microsoft | Purchase payload not sent: UniPixelPurchaseMicrosoft is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}
