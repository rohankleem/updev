<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\client-side-localize-viewcontent.php

function unipixel_localize_viewcontent_for_meta(array $g, $eventTimeStampMs_in) {

    $fbc_value = unipixel_get_fbc_value($eventTimeStampMs_in);

    $clientData = [
        'event_id'     => $g['plcehldr_eventId'],
        'currency'     => $g['plcehldr_currency'],
        'product_id'   => $g['plcehldr_productId'],
        'value'        => $g['plcehldr_price'],
        'content_type' => 'product',
        'content_ids'  => [ strval($g['plcehldr_productId']) ]
    ];

    if (strlen($fbc_value) > 5) {
        $clientData['fbc'] = $fbc_value;
    } else {
        // Don't include fbc key at all
        unset($clientData['fbc']);
    }

    wp_localize_script('unipixel-common', 'UniPixelViewContentMeta', $clientData);
}

function unipixel_localize_viewcontent_for_google(array $g) {


    // GA4 attaches client_id itself; only mirror the same event_id for dedup.

    $clientData = [
        'event_id'   => $g['plcehldr_eventId'],
        'currency'   => $g['plcehldr_currency'],
        'product_id' => $g['plcehldr_productId'],
        'item_name'  => $g['plcehldr_itemName'],
        'price'      => $g['plcehldr_price'],
        'value'      => (float)$g['plcehldr_price'],
    ];

    $variant = $g['plcehldr_variant'] ?? '';
    if ($variant !== '') {
        $clientData['item_variant'] = $variant;
    }

    $gclid = unipixel_get_gclid_value();
    if (! empty($gclid)) {
        $clientData['gclid'] = $gclid;
    }

    wp_localize_script('unipixel-common', 'UniPixelViewContentGoogle', $clientData);
}


/**
 * Localize ViewContent event data for TikTok.
 *
 * @param array $g Generic WooCommerce placeholders.
 * @return void
 */
function unipixel_localize_viewcontent_for_tiktok(array $g)
{
    // Retrieve TikTok identifiers (_ttp and ttclid)
    $tt = unipixel_get_tt_value();

    $clientData = [
        'event_id'     => $g['plcehldr_eventId'] ?? '',
        'currency'     => $g['plcehldr_currency'] ?? '',
        'product_id'   => $g['plcehldr_productId'] ?? '',
        'item_name'    => $g['plcehldr_itemName'] ?? '',
        'value'        => (float) ($g['plcehldr_price'] ?? 0),
        'content_type' => 'product',
        'content_ids'  => [ strval($g['plcehldr_productId'] ?? '') ],
        'ttclid'       => $tt['ttclid'] ?? '',
        'ttp'   => $tt['ttp_cookie'] ?? '',
    ];

    // Push to localized JS object (same handle as other platforms)
    wp_localize_script('unipixel-common', 'UniPixelViewContentTikTok', $clientData);
}


function unipixel_localize_viewcontent_for_pinterest(array $g)
{
    $clientData = [
        'event_id'     => $g['plcehldr_eventId'] ?? '',
        'currency'     => $g['plcehldr_currency'] ?? '',
        'product_id'   => $g['plcehldr_productId'] ?? '',
        'item_name'    => $g['plcehldr_itemName'] ?? '',
        'value'        => (float) ($g['plcehldr_price'] ?? 0),
        'content_ids'  => [(string) ($g['plcehldr_productId'] ?? '')],
    ];

    wp_localize_script('unipixel-common', 'UniPixelViewContentPinterest', $clientData);
}


function unipixel_localize_viewcontent_for_microsoft(array $g, $eventTimeStampMs_in = null)
{
    $msclkid = unipixel_get_msclkid_value();

    $clientData = [
        'event_id'     => $g['plcehldr_eventId'],
        'product_id'   => $g['plcehldr_productId'],
        'item_name'    => $g['plcehldr_itemName'],
        'price'        => $g['plcehldr_price'],
        'currency'     => $g['plcehldr_currency'],
    ];

    if (!empty($msclkid)) {
        $clientData['msclkid'] = $msclkid;
    }

    wp_localize_script('unipixel-common', 'UniPixelViewContentMicrosoft', $clientData);
}

