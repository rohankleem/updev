<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\get-common-woo-data-addtocart.php

/**
 * Prepares generic placeholder data for an AddToCart event.
 *
 * @param int $product_id The WooCommerce product ID.
 * @param int $quantity   The quantity added.
 * @return array|null     An associative array of generic placeholders, or null on failure.
 */
function unipixel_get_common_woo_data_addtocart($product_id, $quantity)
{

    if (!class_exists('WooCommerce')) {
        return null;
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return null;
    }

    // Generate a unique event ID for deduplication
    $plcehldr_event_id = 'add_to_cart_' . microtime(true);

    // Get product price (if needed)
    $plcehldr_price = (float) $product->get_price();

    $plcehldr_currency = get_woocommerce_currency();

    $plcehldr_cart_total = WC()->cart ? (float) WC()->cart->get_total('edit') : 0;

    $itemName = sanitize_text_field($product->get_name());

    $variant = unipixel_get_product_variant($product);
    $arrCategories = unipixel_get_product_categories($product);

    // Advanced Matching — hashed PII from logged-in user (no order available)
    $amData = unipixel_get_advanced_matching_data();

    // Build a minimal placeholder data array.
    $genericData = array_merge($amData, [
        'plcehldr_eventId'    => $plcehldr_event_id,
        'plcehldr_productId'  => (string) $product_id,
        'plcehldr_itemName'   => (string) $itemName,
        'plcehldr_variant'    => (string) $variant,
        'plcehldr_categories' => $arrCategories,
        'plcehldr_quantity'   => (int)$quantity,
        'plcehldr_price'      => $plcehldr_price,
        'plcehldr_currency'   => $plcehldr_currency,
        'plcehldr_cartTotal'  => $plcehldr_cart_total,
        // Environment data (for server-side advanced matching, if desired)
        'plcehldr_clientIp'   => sanitize_text_field(unipixel_get_ip_address() ?? ''),
        'plcehldr_userAgent'  => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'plcehldr_fbpCookie'  => sanitize_text_field($_COOKIE['_fbp'] ?? ''),
    ]);

    return $genericData;
}

