<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-send-viewcontent.php

function unipixel_inline_script_meta_viewcontent($genericData = null)
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(1, 'ViewContent')) {
        return;
    }

    $script = "
        if (typeof UniPixelViewContentMeta !== 'undefined' && typeof fbq !== 'undefined') {
            var payload = {
                content_ids: UniPixelViewContentMeta.content_ids,
                content_type: UniPixelViewContentMeta.content_type,
                currency: UniPixelViewContentMeta.currency,
                value: UniPixelViewContentMeta.price
            };

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | ViewContent object:', UniPixelViewContentMeta);
                // UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | ViewContent data sent:', payload);
            }

            fbq('track', 'ViewContent', payload, { eventID: UniPixelViewContentMeta.event_id });
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Meta | ViewContent payload not sent: UniPixelViewContentMeta or fbq is undefined');
            }
        }
        ";


    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_google_viewcontent($genericData = null)
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(4, 'view_item')) {
        return;
    }

    $opts = get_option('unipixel_logging_options', []);
    $enableGoogleDebugViewClientSide = ! empty($opts['enableGoogleDebugViewClientSide']);

    $googleDebugViewJs = $enableGoogleDebugViewClientSide ? "payload.debug_mode = true;" : "";

    $script = "
        if (typeof UniPixelViewContentGoogle !== 'undefined') {
            var payload = {
                event_id: UniPixelViewContentGoogle.event_id,
                items: [{
                    item_id: UniPixelViewContentGoogle.product_id,
                    item_name: UniPixelViewContentGoogle.item_name,
                    price: UniPixelViewContentGoogle.price
                }],
                currency: UniPixelViewContentGoogle.currency,
                value: UniPixelViewContentGoogle.value,
                event_send_method: 'clientSecond'
            };

            if (UniPixelViewContentGoogle.item_variant) {
                payload.items[0].item_variant = UniPixelViewContentGoogle.item_variant;
            }

            if (UniPixelViewContentGoogle.gclid) {
                payload.gclid = UniPixelViewContentGoogle.gclid;
            }

            {$googleDebugViewJs}

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | view_item object:', UniPixelViewContentGoogle);
                // UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | view_item data sent:', payload);
            }

            if (typeof gtag !== 'undefined') {
                gtag('event', 'view_item', payload);
            } else if (typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)) {
                window.dataLayer.push(
                    Object.assign({ event: 'view_item' }, payload)
                );
            } else {
                if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                    UniPixelConsoleLogger.error('Google | view_item payload not sent: no Google tracking method detected.');
                }
            }
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Google | view_item payload not sent: UniPixelViewContentGoogle is undefined');
            }
        }
        ";


    wp_add_inline_script('unipixel-common', $script, 'after');
}




function unipixel_inline_script_tiktok_viewcontent($genericData = null)
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(3, 'ViewContent')) {
        return;
    }

    $script = "
        if (typeof UniPixelViewContentTikTok !== 'undefined' && typeof ttq !== 'undefined') {
            var payload = {
                content_id:   UniPixelViewContentTikTok.product_id,
                content_type: UniPixelViewContentTikTok.content_type || 'product',
                content_name: UniPixelViewContentTikTok.item_name || '',
                currency:     UniPixelViewContentTikTok.currency,
                value:        UniPixelViewContentTikTok.value
            };

            if (UniPixelViewContentTikTok.ttclid) {
                payload.ttclid = UniPixelViewContentTikTok.ttclid;
            }
            if (UniPixelViewContentTikTok.ttp) {
                payload.ttp = UniPixelViewContentTikTok.ttp;
            }

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'TikTok | Client-side event sent | ViewContent object:', UniPixelViewContentTikTok);
            }

            ttq.track('ViewContent', payload, { event_id: UniPixelViewContentTikTok.event_id });
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('TikTok | ViewContent payload not sent: UniPixelViewContentTikTok or ttq is undefined');
            }
        }
    ";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_pinterest_viewcontent($genericData = null)
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(2, 'view_content')) {
        return;
    }

    $script = "
    if (typeof UniPixelViewContentPinterest !== 'undefined' && typeof pintrk === 'function') {
        var payload = {
            value:       UniPixelViewContentPinterest.value,
            currency:    UniPixelViewContentPinterest.currency,
            content_ids: UniPixelViewContentPinterest.content_ids
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Pinterest | Client-side event sent | ViewContent object:', UniPixelViewContentPinterest);
        }

        pintrk('track', 'viewcontent', payload, { event_id: UniPixelViewContentPinterest.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Pinterest | ViewContent payload not sent: UniPixelViewContentPinterest or pintrk is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}


function unipixel_inline_script_microsoft_viewcontent()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(5, 'view_item')) {
        return;
    }

    $script = "
    if (typeof UniPixelViewContentMicrosoft !== 'undefined') {
        window.uetq = window.uetq || [];

        var payload = {
            event_id:        UniPixelViewContentMicrosoft.event_id,
            ecomm_pagetype:  'product',
            currency:        UniPixelViewContentMicrosoft.currency,
            ecomm_totalvalue: UniPixelViewContentMicrosoft.price,
            items: [{
                id:   UniPixelViewContentMicrosoft.product_id,
                name: UniPixelViewContentMicrosoft.item_name,
                price: UniPixelViewContentMicrosoft.price
            }]
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Microsoft | Client-side event sent | ViewContent object:', UniPixelViewContentMicrosoft);
        }

        window.uetq.push('event', 'view_item', payload);
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Microsoft | ViewContent payload not sent: UniPixelViewContentMicrosoft is undefined');
        }
    }
";

    wp_add_inline_script('unipixel-common', $script, 'after');
}

