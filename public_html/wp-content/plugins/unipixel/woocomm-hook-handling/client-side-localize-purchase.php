<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-localize-purchase.php

function unipixel_localize_purchase_for_meta(array $g, $eventTimeStampMs_in)
{

    $fbc_value = unipixel_get_fbc_value($eventTimeStampMs_in);

    //give the front end everything needed for fbq('track','Purchase')
    $clientData = [
        'event_id'       => $g['plcehldr_eventId'],
        'currency'       => $g['plcehldr_currency'],
        'value'          => $g['plcehldr_value'],
        'transaction_id' => $g['plcehldr_transactionId'],
        'tax'            => $g['plcehldr_tax'],
        'shipping'       => $g['plcehldr_shipping'],
        'contents'       => array_map(function ($item) {
            return [
                'id'       => $item['plcehldr_itemId'],
                'quantity' => $item['plcehldr_quantity'],
                'price'    => $item['plcehldr_price'],
            ];
        }, $g['plcehldr_lineItems']),
        'content_type'   => 'product',
        'content_ids'    => array_map(function($item) {
            return strval($item['plcehldr_itemId']);
        }, $g['plcehldr_lineItems']),
    ];

    if (strlen($fbc_value) > 5) {
        $clientData['fbc'] = $fbc_value;
    } else {
        // Don't include fbc key at all
        unset($clientData['fbc']);
    }

    wp_localize_script('unipixel-common', 'UniPixelPurchaseMeta', $clientData);
}

function unipixel_localize_purchase_for_google(array $g)
{

    $clientData = [
        'event_id'       => $g['plcehldr_eventId'],
        'currency'       => $g['plcehldr_currency'],
        'value'          => $g['plcehldr_value'],
        'transaction_id' => $g['plcehldr_transactionId'],
        'tax'            => $g['plcehldr_tax'],
        'shipping'       => $g['plcehldr_shipping'],
        'items' => array_map(function ($item) {
            // Grab all categories, then take the last one as the "deepest"
            $arrCategories     = $item['plcehldr_categories'] ?? [];
            $deepestCategory   = '';
            if (! empty($arrCategories)) {
                $deepestCategory = array_pop($arrCategories);
            }
            $row = [
                'item_id'       => $item['plcehldr_itemId'],
                'item_name'     => $item['plcehldr_itemName'],
                'quantity'      => $item['plcehldr_quantity'],
                'price'         => $item['plcehldr_price'],
                'item_category' => $deepestCategory,
            ];
            $variant = $item['plcehldr_variant'] ?? '';
            if ($variant !== '') {
                $row['item_variant'] = $variant;
            }
            return $row;
        }, $g['plcehldr_lineItems'])
    ];

    $gclid = unipixel_get_gclid_value();
    if (! empty($gclid)) {
        $clientData['gclid'] = $gclid;
    }

    wp_localize_script('unipixel-common', 'UniPixelPurchaseGoogle', $clientData);
}


function unipixel_localize_purchase_for_tiktok(array $g)
{
    // Retrieve TikTok identifiers (_ttp cookie + ttclid)
    $tt_data      = unipixel_get_tt_value();
    $ttclid_value = $tt_data['ttclid'] ?? '';
    $ttp_cookie   = $tt_data['ttp_cookie'] ?? '';

    // Flat localization; TikTok payload (contents[]) will be built in the send script
    $clientData = [
        'event_id'       => $g['plcehldr_eventId'],
        'currency'       => $g['plcehldr_currency'],
        'value'          => $g['plcehldr_value'],
        'transaction_id' => $g['plcehldr_transactionId'],
        'tax'            => $g['plcehldr_tax'],
        'shipping'       => $g['plcehldr_shipping'],
        'content_type'  => 'product',
        // keep items flat; map to TikTok-ish fields for easy conversion at send time
        'line_items'     => array_map(function ($item) {
            return [
                'content_id'   => $item['plcehldr_itemId'],
                'content_name' => $item['plcehldr_itemName'],
                'content_type' => 'product',
                'quantity'     => $item['plcehldr_quantity'],
                'price'        => $item['plcehldr_price'],
            ];
        }, $g['plcehldr_lineItems']),
    ];

    if (!empty($ttclid_value)) {
        $clientData['ttclid'] = $ttclid_value;
    }

    if (!empty($ttp_cookie)) {
        $clientData['ttp'] = $ttp_cookie;
    }

    wp_localize_script('unipixel-common', 'UniPixelPurchaseTikTok', $clientData);
}


function unipixel_localize_purchase_for_pinterest(array $g)
{
    $clientData = [
        'event_id'       => $g['plcehldr_eventId'] ?? '',
        'currency'       => $g['plcehldr_currency'] ?? '',
        'value'          => $g['plcehldr_value'] ?? 0,
        'order_id'       => $g['plcehldr_transactionId'] ?? '',
        'num_items'      => count($g['plcehldr_lineItems'] ?? []),
        'content_ids'    => array_map(function ($item) {
            return (string) $item['plcehldr_itemId'];
        }, $g['plcehldr_lineItems'] ?? []),
        'contents'       => array_map(function ($item) {
            return [
                'id'         => (string) ($item['plcehldr_itemId'] ?? ''),
                'item_name'  => $item['plcehldr_itemName'] ?? '',
                'item_price' => (string) ($item['plcehldr_price'] ?? 0),
                'quantity'   => (int) ($item['plcehldr_quantity'] ?? 1),
            ];
        }, $g['plcehldr_lineItems'] ?? []),
    ];

    wp_localize_script('unipixel-common', 'UniPixelPurchasePinterest', $clientData);
}


function unipixel_localize_purchase_for_microsoft(array $g, $eventTimeStampMs_in = null)
{
    $msclkid = unipixel_get_msclkid_value();

    $clientData = [
        'event_id'       => $g['plcehldr_eventId'],
        'currency'       => $g['plcehldr_currency'],
        'value'          => $g['plcehldr_value'],
        'transaction_id' => $g['plcehldr_transactionId'],
        'items'          => array_map(function ($item) {
            return [
                'id'       => $item['plcehldr_itemId'],
                'name'     => $item['plcehldr_itemName'],
                'quantity' => $item['plcehldr_quantity'],
                'price'    => $item['plcehldr_price'],
            ];
        }, $g['plcehldr_lineItems']),
        'content_ids'    => array_map(function ($item) {
            return strval($item['plcehldr_itemId']);
        }, $g['plcehldr_lineItems']),
    ];

    if (!empty($msclkid)) {
        $clientData['msclkid'] = $msclkid;
    }

    wp_localize_script('unipixel-common', 'UniPixelPurchaseMicrosoft', $clientData);
}

