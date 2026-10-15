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



