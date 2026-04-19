<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\get-common-woo-data-checkout.php

/**
 * Prepares generic placeholder data for an Initiate Checkout event.
 *
 * @return array|null An associative array of generic placeholders, or null if the cart is empty.
 */
function unipixel_get_common_woo_data_checkout()
{

    if (!class_exists('WooCommerce')) {
        return null;
    }

    // Ensure the cart exists and is not empty.
    if (! WC()->cart || WC()->cart->is_empty()) {
        return null;
    }

    // Generate a unique event ID for deduplication (for both server and client).
    $plcehldr_event_id = 'init_checkout_' . microtime(true);

    // Gather cart totals and currency.
    $plcehldr_currency  = get_woocommerce_currency();
    $plcehldr_value     = (float) WC()->cart->get_total('edit'); // cart total without formatting
    // Cart tax and shipping may be computed differently depending on settings.
    $plcehldr_tax       = (float) WC()->cart->get_cart_contents_tax();
    $plcehldr_shipping  = (float) WC()->cart->get_shipping_total();

    // Prepare line items from the cart.
    $plcehldr_lineItems = [];
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (! isset($cart_item['data'])) {
            continue;
        }
        $product  = $cart_item['data'];
        $quantity = (int) $cart_item['quantity'];
        $price    = (float) $product->get_price();
        $variant = unipixel_get_product_variant($product);
        $arrCategories = unipixel_get_product_categories($product);

        $plcehldr_lineItems[] = [
            'plcehldr_itemId'     => $product->get_id(),
            'plcehldr_itemName'   => sanitize_text_field($product->get_name()),
            'plcehldr_variant'    => (string) $variant,
            'plcehldr_categories' => $arrCategories,
            'plcehldr_quantity'   => $quantity,
            'plcehldr_price'      => $price,
        ];
    }

    // Collect environment details (for advanced matching, if needed).
    $plcehldr_clientIp   = sanitize_text_field(unipixel_get_ip_address() ?? '');
    $plcehldr_userAgent  = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
    $plcehldr_fbpCookie  = sanitize_text_field($_COOKIE['_fbp'] ?? '');

    // Advanced Matching — hashed PII from logged-in user (no order available)
    $amData = unipixel_get_advanced_matching_data();

    return array_merge($amData, [
        'plcehldr_eventId'       => $plcehldr_event_id,
        'plcehldr_currency'      => $plcehldr_currency,
        'plcehldr_value'         => $plcehldr_value,
        'plcehldr_tax'           => $plcehldr_tax,
        'plcehldr_shipping'      => $plcehldr_shipping,
        'plcehldr_lineItems'     => $plcehldr_lineItems,
        'plcehldr_clientIp'      => $plcehldr_clientIp,
        'plcehldr_userAgent'     => $plcehldr_userAgent,
        'plcehldr_fbpCookie'     => $plcehldr_fbpCookie,
    ]);
}
