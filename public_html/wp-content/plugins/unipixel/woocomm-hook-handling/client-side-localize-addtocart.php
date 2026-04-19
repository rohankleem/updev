<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-localize-addtocart.php


// ── Meta ──

function unipixel_get_add_to_cart_client_data_meta(array $g, $eventTimeStampMs_in) {

    $fbc_value = unipixel_get_fbc_value($eventTimeStampMs_in);

    $clientData = [
        'event_id'    => $g['plcehldr_eventId'],
        'currency'    => $g['plcehldr_currency'],
        'product_id'  => $g['plcehldr_productId'],
        'quantity'    => $g['plcehldr_quantity'],
        'price'       => $g['plcehldr_price'],
        'content_type' => 'product',
        'content_ids'  => [ strval($g['plcehldr_productId']) ]
    ];

    if (strlen($fbc_value) > 5) {
        $clientData['fbc'] = $fbc_value;
    }

    return $clientData;
}

function unipixel_localize_add_to_cart_for_meta(array $g, $eventTimeStampMs_in) {
    $clientData = unipixel_get_add_to_cart_client_data_meta($g, $eventTimeStampMs_in);
    wp_localize_script('unipixel-common', 'UniPixelAddToCartMeta', $clientData);
}


// ── Google ──

function unipixel_get_add_to_cart_client_data_google(array $g) {
    $clientData = [
        'event_id'      => $g['plcehldr_eventId'],
        'currency'      => $g['plcehldr_currency'],
        'product_id'    => $g['plcehldr_productId'],
        'item_name'     => $g['plcehldr_itemName'],
        'quantity'      => $g['plcehldr_quantity'],
        'price'         => $g['plcehldr_price'],
        'value'         => (float)$g['plcehldr_price'] * (int)$g['plcehldr_quantity'],
    ];

    $variant = $g['plcehldr_variant'] ?? '';
    if ($variant !== '') {
        $clientData['item_variant'] = $variant;
    }

    $gclid = unipixel_get_gclid_value();
    if (! empty($gclid)) {
        $clientData['gclid'] = $gclid;
    }

    // Include debug mode flag so fragments path can use it
    $opts = get_option('unipixel_logging_options', []);
    $clientData['debug_mode'] = ! empty($opts['enableGoogleDebugViewClientSide']);

    return $clientData;
}

function unipixel_localize_add_to_cart_for_google(array $g) {
    $clientData = unipixel_get_add_to_cart_client_data_google($g);
    wp_localize_script('unipixel-common', 'UniPixelAddToCartGoogle', $clientData);
}


// ── TikTok ──

function unipixel_get_add_to_cart_client_data_tiktok(array $g) {

    // Retrieve both TikTok identifiers (_ttp cookie + ttclid)
    $tt_data = unipixel_get_tt_value();
    $ttclid_value = $tt_data['ttclid'] ?? '';
    $ttp_cookie   = $tt_data['ttp_cookie'] ?? '';

    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'currency'   => $g['plcehldr_currency'],
        'product_id' => $g['plcehldr_productId'],
        'item_name'  => $g['plcehldr_itemName'],
        'quantity'   => $g['plcehldr_quantity'],
        'price'      => $g['plcehldr_price'],
        'content_type'  => 'product',
    ];

    if (!empty($ttclid_value)) {
        $clientData['ttclid'] = $ttclid_value;
    }

    if (!empty($ttp_cookie)) {
        $clientData['ttp'] = $ttp_cookie;
    }

    return $clientData;
}

function unipixel_localize_add_to_cart_for_tiktok(array $g) {
    $clientData = unipixel_get_add_to_cart_client_data_tiktok($g);
    wp_localize_script('unipixel-common', 'UniPixelAddToCartTikTok', $clientData);
}


// ── Pinterest ──

function unipixel_get_add_to_cart_client_data_pinterest(array $g) {
    $clientData = [
        'event_id'     => $g['plcehldr_eventId'] ?? '',
        'currency'     => $g['plcehldr_currency'] ?? '',
        'product_id'   => $g['plcehldr_productId'] ?? '',
        'item_name'    => $g['plcehldr_itemName'] ?? '',
        'quantity'     => $g['plcehldr_quantity'] ?? 1,
        'price'        => $g['plcehldr_price'] ?? 0,
        'value'        => (float) ($g['plcehldr_price'] ?? 0) * (int) ($g['plcehldr_quantity'] ?? 1),
        'content_ids'  => [(string) ($g['plcehldr_productId'] ?? '')],
    ];

    return $clientData;
}

function unipixel_localize_add_to_cart_for_pinterest(array $g)
{
    $clientData = unipixel_get_add_to_cart_client_data_pinterest($g);
    wp_localize_script('unipixel-common', 'UniPixelAddToCartPinterest', $clientData);
}


// ── Microsoft ──

function unipixel_get_add_to_cart_client_data_microsoft(array $g, $eventTimeStampMs_in = null) {
    $msclkid = unipixel_get_msclkid_value();

    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'product_id' => $g['plcehldr_productId'],
        'item_name'  => $g['plcehldr_itemName'],
        'quantity'   => $g['plcehldr_quantity'],
        'price'      => $g['plcehldr_price'],
        'currency'   => $g['plcehldr_currency'],
    ];

    if (!empty($msclkid)) {
        $clientData['msclkid'] = $msclkid;
    }

    return $clientData;
}

function unipixel_localize_add_to_cart_for_microsoft(array $g, $eventTimeStampMs_in = null)
{
    $clientData = unipixel_get_add_to_cart_client_data_microsoft($g, $eventTimeStampMs_in);
    wp_localize_script('unipixel-common', 'UniPixelAddToCartMicrosoft', $clientData);
}
