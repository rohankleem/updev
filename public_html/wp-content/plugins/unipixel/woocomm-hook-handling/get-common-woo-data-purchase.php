<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\get-common-woo-data-purchase.php

/**
 * Collects a WooCommerce order's details into GENERIC placeholders,
 * so they can be mapped for any platform (Meta, Google, etc.).
 *
 * @param int $order_id WooCommerce Order ID
 * @return array|null   Returns an associative array or null if invalid
 */
function unipixel_get_common_woo_data_purchase($order_id)
{

    if (! class_exists('WooCommerce')) {
        return;
    }


    $order = wc_get_order($order_id);
    if (!$order) {
        return null; // invalid order
    }

    // Generate event_id for dedup
    $plcehldr_event_id = 'purchase_' . microtime(true);

    // Advanced Matching — hashed PII from order billing data + logged-in user fallback
    $amData = unipixel_get_advanced_matching_data($order);

    // Gather line items
    $plcehldr_lineItems = [];
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        // 1) discount = subtotal − total
        $subtotal = (float) $item->get_subtotal();
        $total    = (float) $item->get_total();
        $discount = max(0, $subtotal - $total);

        // 2) all categories
        $arrCategories = unipixel_get_product_categories($product);

        // 4) variant attributes (for variations only)
        $variant = unipixel_get_product_variant($product);

        $plcehldr_lineItems[] = [
            'plcehldr_itemId'   => $product->get_id(),
            'plcehldr_itemName' => sanitize_text_field($product->get_name()),
            'plcehldr_quantity' => (int)$item->get_quantity(),
            'plcehldr_price'    => (float)$item->get_total(),
            'plcehldr_discount'     => $discount,
            'plcehldr_categories'   => $arrCategories,    // array of strings
            'plcehldr_variant'      => $variant,
        ];
    }

    return array_merge($amData, [
        // Basic identity
        'plcehldr_eventId'       => $plcehldr_event_id,
        // Env placeholders (server side)
        'plcehldr_clientIp'      => sanitize_text_field(unipixel_get_ip_address() ?? ''),
        'plcehldr_userAgent'     => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'plcehldr_fbpCookie'     => sanitize_text_field($_COOKIE['_fbp'] ?? ''),
        // ECommerce placeholders
        'plcehldr_currency'      => $order->get_currency(),
        'plcehldr_value'         => (float)$order->get_total(),
        'plcehldr_transactionId' => (string)$order_id,
        'plcehldr_tax'           => (float)$order->get_total_tax(),
        'plcehldr_shipping'      => (float)$order->get_shipping_total(),
        'plcehldr_lineItems'     => $plcehldr_lineItems,
    ]);
}
