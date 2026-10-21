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


//GA4 client ID on product, checkout and order-received pages, before the page starts sending
add_action('template_redirect', 'unipixel_ensure_ga_client_id_before_page');

function unipixel_ensure_ga_client_id_before_page() {
    // Google's server-side View item, Begin checkout and Purchase are built partway through these pages,
    // too late to set the _ga cookie on classic themes, so create it here for visitors who don't have one.
    $on_page = (function_exists('is_product') && is_product())
        || (function_exists('is_checkout') && is_checkout())
        || (function_exists('is_order_received_page') && is_order_received_page());
    if (!$on_page) {
        return;
    }
    $google = unipixel_get_platform_settings(4);
    if (empty($google->platform_enabled) || empty($google->serverside_global_enabled)) {
        return;
    }
    if (unipixel_check_for_consent()) {
        return; // consent not given
    }
    unipixel_get_or_create_ga_client_id();
}



