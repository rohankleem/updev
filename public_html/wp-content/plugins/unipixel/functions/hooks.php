<?php

//File: public_html\wp-content\plugins\unipixel\functions\hooks.php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


//capture fbclid
add_action('init', 'unipixel_capture_fbclid');

function unipixel_capture_fbclid() {
    if (isset($_GET['fbclid'])) {
        $fbclid = sanitize_text_field($_GET['fbclid']);
        // Store the raw fbclid in the cookie called "unipixel_fbclid"
        setcookie('unipixel_fbclid', $fbclid, time() + 7776000, COOKIEPATH, COOKIE_DOMAIN); // 90 days — Meta considers fbclid expired after 90 days
        // For immediate access in PHP:
        $_COOKIE['unipixel_fbclid'] = $fbclid;
    }
}


//capture msclkid (Microsoft Click ID)
add_action('init', 'unipixel_capture_msclkid');

function unipixel_capture_msclkid() {
    if (isset($_GET['msclkid'])) {
        $msclkid = sanitize_text_field($_GET['msclkid']);
        setcookie('unipixel_msclkid', $msclkid, time() + 7776000, COOKIEPATH, COOKIE_DOMAIN); // 90 days
        $_COOKIE['unipixel_msclkid'] = $msclkid;
    }
}



add_action('woocommerce_thankyou', 'unipixel_woocommerce_order_completed', 10, 1);

function unipixel_woocommerce_order_completed($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    $currency = $order->get_currency();
    $total = $order->get_total();
    $transaction_id = $order_id;
    $tax = $order->get_total_tax();
    $shipping = $order->get_shipping_total();

    // Capture email and phone from order
    $hashedEmail = $order->get_billing_email();
    $hashedPhone = $order->get_billing_phone();

    // Optionally hash email and phone (use this if you prefer to hash the data before sending it)
    // Note: Use a secure hashing algorithm like SHA256 if required by your integration.
    if ($hashedEmail) {
        $hashedEmail = hash('sha256', strtolower(trim($hashedEmail)));
    }
    if ($hashedPhone) {
        $hashedPhone = hash('sha256', preg_replace('/[^0-9]/', '', $hashedPhone)); // Remove non-numeric characters
    }

    // Get order items
    $items = array();
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $items[] = array(
            'item_id' => $product->get_id(),
            'item_name' => $product->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total()
        );
    }

    // Localize script with detailed order data
    wp_localize_script('unipixel-common', 'UniPixelOrderData', array(
        'currency' => $currency,
        'value' => $total,
        'transaction_id' => $transaction_id,
        'tax' => $tax,
        'shipping' => $shipping,
        'items' => $items,
        'hashedEmail' => $hashedEmail, // Include hashed email
        'hashedPhone' => $hashedPhone  // Include hashed phone
    ));
}
