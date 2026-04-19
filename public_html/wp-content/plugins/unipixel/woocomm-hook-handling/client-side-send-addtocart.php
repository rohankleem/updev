<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-send-addtocart.php


// ═══════════════════════════════════════════════════════════════════════════
// Fragment Collector — accumulates client data during AJAX add-to-cart
// so the woocommerce_add_to_cart_fragments filter can output it all at once.
// Only lives for the duration of a single PHP request — no transients needed.
// ═══════════════════════════════════════════════════════════════════════════

class UniPixel_AddToCart_Fragment_Collector {
	private static $platform_data  = [];
	private static $server_results = [];

	public static function add_platform_data($platform_key, array $client_data) {
		self::$platform_data[$platform_key] = $client_data;
	}

	public static function add_server_result($platform_key, $event_name, array $result_info) {
		self::$server_results[$platform_key] = [
			'event_name' => $event_name,
			'payload'    => $result_info['payload']  ?? [],
			'response'   => $result_info['response'] ?? [],
		];
	}

	public static function get_platform_data()  { return self::$platform_data; }
	public static function get_server_results() { return self::$server_results; }
	public static function has_data()           { return !empty(self::$platform_data); }
}


// ═══════════════════════════════════════════════════════════════════════════
// Fragment placeholder — must exist on the page before the AJAX call so
// WooCommerce's JS can find and replace it with the fragment response.
// ═══════════════════════════════════════════════════════════════════════════

add_action('wp_footer', 'unipixel_addtocart_fragment_placeholder');

function unipixel_addtocart_fragment_placeholder() {
	if (! class_exists('WooCommerce')) {
		return;
	}
	echo '<div class="unipixel-addtocart-fragment" style="display:none;"></div>';
}


// ═══════════════════════════════════════════════════════════════════════════
// Fragments filter — builds the <script> that fires all platform pixels
// after WooCommerce AJAX add-to-cart completes.
// ═══════════════════════════════════════════════════════════════════════════

add_filter('woocommerce_add_to_cart_fragments', 'unipixel_addtocart_fragments', 10, 1);

function unipixel_addtocart_fragments($fragments) {
	if (! UniPixel_AddToCart_Fragment_Collector::has_data()) {
		return $fragments;
	}

	$platform_data  = UniPixel_AddToCart_Fragment_Collector::get_platform_data();
	$server_results = UniPixel_AddToCart_Fragment_Collector::get_server_results();
	$js_parts       = [];

	// ── Client pixel calls per platform ──

	if (isset($platform_data['meta'])) {
		$json = wp_json_encode($platform_data['meta']);
		$js_parts[] = "(function(){
			var d = {$json};
			if (typeof fbq !== 'undefined') {
				var payload = {
					contents: [{ id: d.product_id, quantity: d.quantity, price: d.price }],
					currency: d.currency,
					value: d.price * d.quantity,
					content_type: d.content_type,
					content_ids: d.content_ids
				};
				if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
					UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | AddToCart object:', d);
				}
				fbq('track', 'AddToCart', payload, { eventID: d.event_id });
			}
		})();";
	}

	if (isset($platform_data['google'])) {
		$json = wp_json_encode($platform_data['google']);
		$js_parts[] = "(function(){
			var d = {$json};
			var payload = {
				event_id: d.event_id,
				items: [{ item_id: d.product_id, item_name: d.item_name, quantity: d.quantity, price: d.price }],
				currency: d.currency,
				value: d.price * d.quantity,
				event_send_method: 'clientSecond'
			};
			if (d.item_variant) { payload.items[0].item_variant = d.item_variant; }
			if (d.gclid) { payload.gclid = d.gclid; }
			if (d.debug_mode) { payload.debug_mode = true; }
			if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
				UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | add_to_cart object:', d);
			}
			if (typeof gtag !== 'undefined') {
				gtag('event', 'add_to_cart', payload);
			} else if (typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)) {
				window.dataLayer.push(Object.assign({ event: 'add_to_cart' }, payload));
			}
		})();";
	}

	if (isset($platform_data['tiktok'])) {
		$json = wp_json_encode($platform_data['tiktok']);
		$js_parts[] = "(function(){
			var d = {$json};
			if (typeof ttq !== 'undefined') {
				var payload = {
					contents: [{ content_id: d.product_id, content_name: d.item_name, content_type: 'product', quantity: d.quantity, price: d.price }],
					currency: d.currency,
					value: d.price * d.quantity
				};
				if (d.ttclid) { payload.ttclid = d.ttclid; }
				if (d.ttp) { payload.ttp = d.ttp; }
				if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
					UniPixelConsoleLogger.log('SEND', 'TikTok | Client-side event sent | AddToCart object:', d);
				}
				ttq.track('AddToCart', payload, { event_id: d.event_id });
			}
		})();";
	}

	if (isset($platform_data['pinterest'])) {
		$json = wp_json_encode($platform_data['pinterest']);
		$js_parts[] = "(function(){
			var d = {$json};
			if (typeof pintrk === 'function') {
				var payload = {
					value: d.value,
					currency: d.currency,
					content_ids: d.content_ids,
					num_items: 1
				};
				if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
					UniPixelConsoleLogger.log('SEND', 'Pinterest | Client-side event sent | AddToCart object:', d);
				}
				pintrk('track', 'addtocart', payload, { event_id: d.event_id });
			}
		})();";
	}

	if (isset($platform_data['microsoft'])) {
		$json = wp_json_encode($platform_data['microsoft']);
		$js_parts[] = "(function(){
			var d = {$json};
			window.uetq = window.uetq || [];
			var payload = {
				event_id: d.event_id,
				ecomm_pagetype: 'cart',
				currency: d.currency,
				ecomm_totalvalue: d.price * d.quantity,
				items: [{ id: d.product_id, name: d.item_name, quantity: d.quantity, price: d.price }]
			};
			if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
				UniPixelConsoleLogger.log('SEND', 'Microsoft | Client-side event sent | AddToCart object:', d);
			}
			window.uetq.push('event', 'add_to_cart', payload);
		})();";
	}

	// ── Server result console logging ──

	$platform_labels = [
		'meta'      => 'Meta',
		'google'    => 'Google',
		'tiktok'    => 'TikTok',
		'pinterest' => 'Pinterest',
		'microsoft' => 'Microsoft',
	];

	foreach ($server_results as $key => $sr) {
		$label = $platform_labels[$key] ?? $key;
		$sr_json = wp_json_encode($sr);
		$js_parts[] = "(function(){
			if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
				var sr = {$sr_json};
				UniPixelConsoleLogger.log('SEND', '{$label} | Server-side event sent | ' + sr.event_name + ' object:', sr.payload);
				if (sr.response && Object.keys(sr.response).length > 0) {
					UniPixelConsoleLogger.log('RESPONSE', '{$label} | Platform response | ' + sr.event_name + ' object:', sr.response);
				}
			}
		})();";
	}

	// ── Build the fragment ──

	if (!empty($js_parts)) {
		$combined_js = implode("\n", $js_parts);
		$fragments['div.unipixel-addtocart-fragment'] =
			'<div class="unipixel-addtocart-fragment" style="display:none;">'
			. '<script>' . $combined_js . '</script>'
			. '</div>';
	}

	return $fragments;
}


// ═══════════════════════════════════════════════════════════════════════════
// Per-platform inline script + transient storage (REDIRECT path only)
// These functions are called when $triggerHook === 'woocommerce_add_to_cart'
// (non-AJAX). The AJAX path now uses the fragment collector above.
// ═══════════════════════════════════════════════════════════════════════════


// ── Meta ──

function unipixel_inline_script_meta_addtocart($triggerHook = "", $genericData = null)
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(1, 'AddToCart')) {
        return;
    }

    $script = "
        if (typeof UniPixelAddToCartMeta !== 'undefined' && typeof fbq !== 'undefined') {
            var payload = {
                contents: [{
                    id: UniPixelAddToCartMeta.product_id,
                    quantity: UniPixelAddToCartMeta.quantity,
                    price: UniPixelAddToCartMeta.price
                }],
                currency: UniPixelAddToCartMeta.currency,
                value: UniPixelAddToCartMeta.price * UniPixelAddToCartMeta.quantity,
                content_type: UniPixelAddToCartMeta.content_type,
                content_ids: UniPixelAddToCartMeta.content_ids
            };

            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | AddToCart object:', UniPixelAddToCartMeta);
                // UniPixelConsoleLogger.log('SEND', 'Meta | Client-side event sent | AddToCart data sent:', payload);
            }

            fbq('track', 'AddToCart', payload, { eventID: UniPixelAddToCartMeta.event_id });
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Meta | AddToCart payload not sent: UniPixelAddToCartMeta or fbq is undefined');
            }
        }
        ";

    if ($triggerHook === 'woocommerce_add_to_cart') {
        $user_identifier = unipixel_get_user_identifier_for_transient();
        $transient_key = 'unipixel_addtocart_event_meta_' . $user_identifier;

        set_transient($transient_key, array(
            'genericData' => $genericData,
            'script'      => $script
        ), HOUR_IN_SECONDS);
    } else {
        wp_add_inline_script('unipixel-common', $script, 'after');
    }
}

function unipixel_handle_session_addtocart_meta()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(1, 'AddToCart')) {
        return;
    }

    $user_identifier = unipixel_get_user_identifier_for_transient();
    $transient_key = 'unipixel_addtocart_event_meta_' . $user_identifier;

    // Attempt to get the transient.
    if (false !== ($data = get_transient($transient_key))) {
        $script = isset($data['script']) ? $data['script'] : '';
        $genericData = isset($data['genericData']) ? $data['genericData'] : null;

        $eventTimeStampMs = (int) round(microtime(true) * 1000);

        if (!empty($genericData)) {
            // Pass the event time to the localization function.
            unipixel_localize_add_to_cart_for_meta($genericData, $eventTimeStampMs);
        }

        $wrappedScript = "jQuery(document).ready(function() { " . $script . " });";
        wp_add_inline_script('unipixel-common', $wrappedScript, 'after');

        // Clear the transient now that it's been used.
        delete_transient($transient_key);
    }
}
add_action('wp_footer', 'unipixel_handle_session_addtocart_meta');


// ── Google ──

function unipixel_inline_script_google_addtocart($triggerHook = "", $genericData = null)
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(4, 'add_to_cart')) {
        return;
    }

    $opts = get_option('unipixel_logging_options', []);
    $enableGoogleDebugViewClientSide = ! empty($opts['enableGoogleDebugViewClientSide']);

    $googleDebugViewJs = $enableGoogleDebugViewClientSide ? "payload.debug_mode = true;" : "";

    $script = "
    if (typeof UniPixelAddToCartGoogle !== 'undefined') {
        var payload = {
            event_id: UniPixelAddToCartGoogle.event_id,
            items: [{
                item_id: UniPixelAddToCartGoogle.product_id,
                item_name: UniPixelAddToCartGoogle.item_name,
                quantity: UniPixelAddToCartGoogle.quantity,
                price: UniPixelAddToCartGoogle.price
            }],
            currency: UniPixelAddToCartGoogle.currency,
            value: UniPixelAddToCartGoogle.price * UniPixelAddToCartGoogle.quantity,
            event_send_method: 'clientSecond'
        };

        if (UniPixelAddToCartGoogle.item_variant) {
            payload.items[0].item_variant = UniPixelAddToCartGoogle.item_variant;
        }

        if (UniPixelAddToCartGoogle.gclid) {
            payload.gclid = UniPixelAddToCartGoogle.gclid;
        }

        {$googleDebugViewJs}

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | add_to_cart object:', UniPixelAddToCartGoogle);
            // UniPixelConsoleLogger.log('SEND', 'Google | Client-side event sent | add_to_cart data sent:', payload);
        }

        if (typeof gtag !== 'undefined') {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Google AddToCart: using gtag() branch');
            }
            gtag('event', 'add_to_cart', payload);
        } else if (typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)) {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.log('SEND', 'Google AddToCart: using dataLayer.push branch');
            }
            window.dataLayer.push(
                Object.assign({ event: 'add_to_cart' }, payload)
            );
        } else {
            if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
                UniPixelConsoleLogger.error('Google AddToCart: no tracking method detected');
            }
        }
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Google | add_to_cart payload not sent: UniPixelAddToCartGoogle is undefined');
        }
    }
    ";


    if ($triggerHook === 'woocommerce_add_to_cart') {
        $user_identifier = unipixel_get_user_identifier_for_transient();
        $transient_key = 'unipixel_addtocart_event_google_' . $user_identifier;
        set_transient($transient_key, array(
            'genericData' => $genericData,
            'script'      => $script
        ), HOUR_IN_SECONDS);
    } else {
        wp_add_inline_script('unipixel-common', $script, 'after');
    }
}

function unipixel_handle_session_addtocart_google()
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(4, 'add_to_cart')) {
        return;
    }

    $user_identifier = unipixel_get_user_identifier_for_transient();
    $transient_key = 'unipixel_addtocart_event_google_' . $user_identifier;

    $data = get_transient($transient_key);
    if ($data !== false) {
        $script = isset($data['script']) ? $data['script'] : '';
        $genericData = isset($data['genericData']) ? $data['genericData'] : null;

        // Re-localize generic event data for Google tracking if genericData is available.
        if (!empty($genericData)) {
            unipixel_localize_add_to_cart_for_google($genericData);
        }

        // Wrap the stored script in a document ready block.
        $wrappedScript = "jQuery(document).ready(function() { " . $script . " });";
        wp_add_inline_script('unipixel-common', $wrappedScript, 'after');

        // Delete the transient to avoid duplicate firing.
        delete_transient($transient_key);
    }
}
add_action('wp_footer', 'unipixel_handle_session_addtocart_google');


// ── TikTok ──

function unipixel_inline_script_tiktok_addtocart($triggerHook = "", $genericData = null)
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    // Platform ID 3 = TikTok
    if (! unipixel_is_woo_event_enabled(3, 'AddToCart')) {
        return;
    }


    $script = "
    if (typeof UniPixelAddToCartTikTok !== 'undefined' && typeof ttq !== 'undefined') {
        var payload = {
            contents: [{
                content_id:   UniPixelAddToCartTikTok.product_id,
                content_name: UniPixelAddToCartTikTok.item_name,
                content_type: 'product',
                quantity:     UniPixelAddToCartTikTok.quantity,
                price:        UniPixelAddToCartTikTok.price
            }],
            currency: UniPixelAddToCartTikTok.currency,
            value:    UniPixelAddToCartTikTok.price * UniPixelAddToCartTikTok.quantity
        };

        if (UniPixelAddToCartTikTok.ttclid) {
            payload.ttclid = UniPixelAddToCartTikTok.ttclid;
        }
        if (UniPixelAddToCartTikTok.ttp) {
            payload.ttp = UniPixelAddToCartTikTok.ttp;
        }

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'TikTok | Client-side event sent | AddToCart object:', UniPixelAddToCartTikTok);
        }

        ttq.track('AddToCart', payload, { event_id: UniPixelAddToCartTikTok.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('TikTok | AddToCart payload not sent: UniPixelAddToCartTikTok or ttq is undefined');
        }
    }
";


    // --- Store or output inline script depending on hook context ---
    if ($triggerHook === 'woocommerce_add_to_cart') {
        $user_identifier = unipixel_get_user_identifier_for_transient();
        $transient_key = 'unipixel_addtocart_event_tiktok_' . $user_identifier;

        set_transient($transient_key, array(
            'genericData' => $genericData,
            'script'      => $script
        ), HOUR_IN_SECONDS);
    } else {
        wp_add_inline_script('unipixel-common', $script, 'after');
    }
}


function unipixel_handle_session_addtocart_tiktok()
{

    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(3, 'AddToCart')) {
        return;
    }

    $user_identifier = unipixel_get_user_identifier_for_transient();
    $transient_key = 'unipixel_addtocart_event_tiktok_' . $user_identifier;

    // Attempt to get the transient.
    if (false !== ($data = get_transient($transient_key))) {
        $script = isset($data['script']) ? $data['script'] : '';
        $genericData = isset($data['genericData']) ? $data['genericData'] : null;

        if (!empty($genericData)) {
            // Localize the TikTok AddToCart data for the current page.
            unipixel_localize_add_to_cart_for_tiktok($genericData);
        }

        $wrappedScript = "jQuery(document).ready(function() { " . $script . " });";
        wp_add_inline_script('unipixel-common', $wrappedScript, 'after');

        // Clear the transient once it's used.
        delete_transient($transient_key);
    }
}
add_action('wp_footer', 'unipixel_handle_session_addtocart_tiktok');



// ── Server result console logging recovery (redirect path) ──

add_action('wp_footer', function () {
    $uid = unipixel_get_user_identifier_for_transient();

    // Meta
    $k = 'unipixel_addtocart_server_meta_' . $uid;
    if (false !== ($data = get_transient($k))) {
        unipixel_localize_console_logging_for_meta('AddToCart', $data);
        delete_transient($k);
    }

    // Google
    $k = 'unipixel_addtocart_server_google_' . $uid;
    if (false !== ($data = get_transient($k))) {
        unipixel_localize_console_logging_for_google('add_to_cart', $data);
        delete_transient($k);
    }

    // TikTok
    $k = 'unipixel_addtocart_server_tiktok_' . $uid;
    if (false !== ($data = get_transient($k))) {
        unipixel_localize_console_logging_for_tiktok('AddToCart', $data);
        delete_transient($k);
    }

    // Pinterest
    $k = 'unipixel_addtocart_server_pinterest_' . $uid;
    if (false !== ($data = get_transient($k))) {
        unipixel_localize_console_logging_for_pinterest('add_to_cart', $data);
        delete_transient($k);
    }

    // Microsoft
    $k = 'unipixel_addtocart_server_microsoft_' . $uid;
    if (false !== ($data = get_transient($k))) {
        unipixel_localize_console_logging_for_microsoft('add_to_cart', $data);
        delete_transient($k);
    }
});


// ── Pinterest ──

function unipixel_inline_script_pinterest_addtocart($triggerHook = "", $genericData = null)
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(2, 'add_to_cart')) {
        return;
    }

    $script = "
    if (typeof UniPixelAddToCartPinterest !== 'undefined' && typeof pintrk === 'function') {
        var payload = {
            value:       UniPixelAddToCartPinterest.value,
            currency:    UniPixelAddToCartPinterest.currency,
            content_ids: UniPixelAddToCartPinterest.content_ids,
            num_items:   1
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Pinterest | Client-side event sent | AddToCart object:', UniPixelAddToCartPinterest);
        }

        pintrk('track', 'addtocart', payload, { event_id: UniPixelAddToCartPinterest.event_id });
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Pinterest | AddToCart payload not sent: UniPixelAddToCartPinterest or pintrk is undefined');
        }
    }
";

    if ($triggerHook === 'woocommerce_add_to_cart') {
        $user_identifier = unipixel_get_user_identifier_for_transient();
        $transient_key = 'unipixel_addtocart_event_pinterest_' . $user_identifier;
        set_transient($transient_key, array(
            'genericData' => $genericData,
            'script'      => $script
        ), HOUR_IN_SECONDS);
    } else {
        wp_add_inline_script('unipixel-common', $script, 'after');
    }
}

function unipixel_handle_session_addtocart_pinterest()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(2, 'add_to_cart')) {
        return;
    }

    $user_identifier = unipixel_get_user_identifier_for_transient();
    $transient_key = 'unipixel_addtocart_event_pinterest_' . $user_identifier;

    if (false !== ($data = get_transient($transient_key))) {
        $script = isset($data['script']) ? $data['script'] : '';
        $genericData = isset($data['genericData']) ? $data['genericData'] : null;

        if (!empty($genericData)) {
            unipixel_localize_add_to_cart_for_pinterest($genericData);
        }

        $wrappedScript = "jQuery(document).ready(function() { " . $script . " });";
        wp_add_inline_script('unipixel-common', $wrappedScript, 'after');

        delete_transient($transient_key);
    }
}
add_action('wp_footer', 'unipixel_handle_session_addtocart_pinterest');


// ── Microsoft ──

function unipixel_inline_script_microsoft_addtocart($triggerHook = '', $genericData = null)
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    // Platform ID 5 = Microsoft
    if (! unipixel_is_woo_event_enabled(5, 'add_to_cart')) {
        return;
    }

    $script = "
    if (typeof UniPixelAddToCartMicrosoft !== 'undefined') {
        window.uetq = window.uetq || [];

        var payload = {
            event_id:       UniPixelAddToCartMicrosoft.event_id,
            ecomm_pagetype: 'cart',
            currency:       UniPixelAddToCartMicrosoft.currency,
            ecomm_totalvalue: UniPixelAddToCartMicrosoft.price * UniPixelAddToCartMicrosoft.quantity,
            items: [{
                id:       UniPixelAddToCartMicrosoft.product_id,
                name:     UniPixelAddToCartMicrosoft.item_name,
                quantity: UniPixelAddToCartMicrosoft.quantity,
                price:    UniPixelAddToCartMicrosoft.price
            }]
        };

        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.log('SEND', 'Microsoft | Client-side event sent | AddToCart object:', UniPixelAddToCartMicrosoft);
        }

        window.uetq.push('event', 'add_to_cart', payload);
    } else {
        if (window.__UniPixelConsoleActive && typeof UniPixelConsoleLogger !== 'undefined') {
            UniPixelConsoleLogger.error('Microsoft | AddToCart payload not sent: UniPixelAddToCartMicrosoft is undefined');
        }
    }
";

    if ($triggerHook === 'woocommerce_add_to_cart') {
        $user_identifier = unipixel_get_user_identifier_for_transient();
        $transient_key = 'unipixel_addtocart_event_microsoft_' . $user_identifier;
        set_transient($transient_key, array(
            'genericData' => $genericData,
            'script'      => $script
        ), HOUR_IN_SECONDS);
    } else {
        wp_add_inline_script('unipixel-common', $script, 'after');
    }
}

function unipixel_handle_session_addtocart_microsoft()
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    if (! unipixel_is_woo_event_enabled(5, 'add_to_cart')) {
        return;
    }

    $user_identifier = unipixel_get_user_identifier_for_transient();
    $transient_key = 'unipixel_addtocart_event_microsoft_' . $user_identifier;

    if (false !== ($data = get_transient($transient_key))) {
        $script = isset($data['script']) ? $data['script'] : '';
        $genericData = isset($data['genericData']) ? $data['genericData'] : null;

        if (!empty($genericData)) {
            unipixel_localize_add_to_cart_for_microsoft($genericData);
        }

        $wrappedScript = "jQuery(document).ready(function() { " . $script . " });";
        wp_add_inline_script('unipixel-common', $wrappedScript, 'after');

        delete_transient($transient_key);
    }
}
add_action('wp_footer', 'unipixel_handle_session_addtocart_microsoft');
