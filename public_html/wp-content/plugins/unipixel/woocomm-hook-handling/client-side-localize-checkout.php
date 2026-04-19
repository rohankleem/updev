<?php 

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-localize-checkout.php

function unipixel_localize_initiate_checkout_for_meta(array $g, $eventTimeStampMs_in) {

    $fbc_value = unipixel_get_fbc_value($eventTimeStampMs_in);

    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'currency'   => $g['plcehldr_currency'],
        'value'      => $g['plcehldr_value'],
        'tax'        => $g['plcehldr_tax'],
        'shipping'   => $g['plcehldr_shipping'],
        'contents'   => array_map(function($item) {
            return [
                'id'       => $item['plcehldr_itemId'],
                'quantity' => $item['plcehldr_quantity'],
                'price'    => $item['plcehldr_price'],
            ];
        }, $g['plcehldr_lineItems']),
        'content_type'  => 'product',
        'content_ids'   => array_map(function($item) {
            return strval($item['plcehldr_itemId']);
        }, $g['plcehldr_lineItems']),
    ];

    if (strlen($fbc_value) > 5) {
        $clientData['fbc'] = $fbc_value;
    } else {
        // Don't include fbc key at all
        unset($clientData['fbc']);
    }
    wp_localize_script('unipixel-common', 'UniPixelInitiateCheckoutMeta', $clientData);
    
}

function unipixel_localize_initiate_checkout_for_google(array $g) {
    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'currency'   => $g['plcehldr_currency'],
        'value'      => $g['plcehldr_value'],
        'tax'        => $g['plcehldr_tax'],
        'shipping'   => $g['plcehldr_shipping'],
        'items'      => array_map(function($item) {
            $row = [
                'item_id'       => $item['plcehldr_itemId'],
                'item_name'     => $item['plcehldr_itemName'],
                'quantity'      => $item['plcehldr_quantity'],
                'price'         => $item['plcehldr_price'],
            ];
            $variant = $item['plcehldr_variant'] ?? '';
            if ($variant !== '') {
                $row['item_variant'] = $variant;
            }
            return $row;
        }, $g['plcehldr_lineItems']),
    ];

    $gclid = unipixel_get_gclid_value();
    if (! empty($gclid)) {
        $clientData['gclid'] = $gclid;
    }
    
    wp_localize_script('unipixel-common', 'UniPixelInitiateCheckoutGoogle', $clientData);
    
}


function unipixel_localize_initiate_checkout_for_tiktok(array $g) {

    // Retrieve TikTok identifiers
    $tt_data      = unipixel_get_tt_value();
    $ttclid_value = $tt_data['ttclid'] ?? '';
    $ttp_cookie   = $tt_data['ttp_cookie'] ?? '';

    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'currency'   => $g['plcehldr_currency'],
        'value'      => $g['plcehldr_value'],
        'tax'        => $g['plcehldr_tax'],
        'shipping'   => $g['plcehldr_shipping'],
        'content_type'  => 'product',
    ];

    if (!empty($ttclid_value)) {
        $clientData['ttclid'] = $ttclid_value;
    }

    if (!empty($ttp_cookie)) {
        $clientData['ttp'] = $ttp_cookie;
    }

    // Keep line item data flat (TikTok payload will be built in the send script)
    $clientData['line_items'] = array_map(function($item) {
        return [
            'content_id'   => $item['plcehldr_itemId'],
            'content_name' => $item['plcehldr_itemName'],
            'content_type' => 'product',
            'quantity'     => $item['plcehldr_quantity'],
            'price'        => $item['plcehldr_price'],
        ];
    }, $g['plcehldr_lineItems']);

    wp_localize_script('unipixel-common', 'UniPixelInitiateCheckoutTikTok', $clientData);
}


function unipixel_localize_initiate_checkout_for_pinterest(array $g)
{
    $clientData = [
        'event_id'    => $g['plcehldr_eventId'] ?? '',
        'currency'    => $g['plcehldr_currency'] ?? '',
        'value'       => $g['plcehldr_value'] ?? 0,
        'num_items'   => count($g['plcehldr_lineItems'] ?? []),
        'content_ids' => array_map(function ($item) {
            return (string) $item['plcehldr_itemId'];
        }, $g['plcehldr_lineItems'] ?? []),
        'contents'    => array_map(function ($item) {
            return [
                'id'         => (string) ($item['plcehldr_itemId'] ?? ''),
                'item_name'  => $item['plcehldr_itemName'] ?? '',
                'item_price' => (string) ($item['plcehldr_price'] ?? 0),
                'quantity'   => (int) ($item['plcehldr_quantity'] ?? 1),
            ];
        }, $g['plcehldr_lineItems'] ?? []),
    ];

    wp_localize_script('unipixel-common', 'UniPixelInitiateCheckoutPinterest', $clientData);
}


function unipixel_localize_checkout_for_microsoft(array $g, $eventTimeStampMs_in = null)
{
    $msclkid = unipixel_get_msclkid_value();

    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'currency'   => $g['plcehldr_currency'],
        'value'      => $g['plcehldr_value'],
        'items'      => array_map(function ($item) {
            return [
                'id'       => $item['plcehldr_itemId'],
                'name'     => $item['plcehldr_itemName'],
                'quantity' => $item['plcehldr_quantity'],
                'price'    => $item['plcehldr_price'],
            ];
        }, $g['plcehldr_lineItems']),
    ];

    if (!empty($msclkid)) {
        $clientData['msclkid'] = $msclkid;
    }

    wp_localize_script('unipixel-common', 'UniPixelCheckoutMicrosoft', $clientData);
}

