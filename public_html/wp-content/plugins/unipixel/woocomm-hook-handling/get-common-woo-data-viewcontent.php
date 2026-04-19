<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\get-common-woo-data-viewcontent.php

/**
 * Prepares generic placeholder data for a ViewContent event.
 *
 * @param int $product_id The WooCommerce product ID.
 * @return array|null     An associative array of generic placeholders, or null on failure.
 */
function unipixel_get_common_woo_data_viewcontent($product_id)
{
    if (!class_exists('WooCommerce')) {
        return null;
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return null;
    }

    // Generate a unique event ID for deduplication
    $plcehldr_event_id = 'view_content_' . microtime(true);

    $plcehldr_price = (float) $product->get_price();
    $plcehldr_currency = get_woocommerce_currency();

    $itemName = sanitize_text_field($product->get_name());
    $variant = unipixel_get_product_variant($product);
    $arrCategories = unipixel_get_product_categories($product);

    // Advanced Matching — hashed PII from logged-in user (no order available)
    $amData = unipixel_get_advanced_matching_data();

    $genericData = array_merge($amData, [
        'plcehldr_eventId'    => $plcehldr_event_id,
        'plcehldr_productId'  => (string) $product_id,
        'plcehldr_itemName'   => (string) $itemName,
        'plcehldr_variant'    => (string) $variant,
        'plcehldr_categories' => $arrCategories,
        'plcehldr_price'      => $plcehldr_price,
        'plcehldr_currency'   => $plcehldr_currency,
        'plcehldr_clientIp'   => sanitize_text_field(unipixel_get_ip_address() ?? ''),
        'plcehldr_userAgent'  => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'plcehldr_fbpCookie'  => sanitize_text_field($_COOKIE['_fbp'] ?? ''),
    ]);

    return $genericData;
}
