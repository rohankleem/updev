<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\prepare-common-to-platform-purchase.php

/**
 * Maps the generic placeholders to "user_data" + "custom_data" + "event_id"
 * specifically for Meta's Conversions API (Purchase event).
 */
function unipixel_prepare_common_to_platform_purchase_meta(array $g)
{
    $fbc_value = unipixel_get_fbc_value();

    $pageUrl = unipixel_get_current_page_url();

    $user_data = [
        'client_ip_address' => $g['plcehldr_clientIp'],
        'client_user_agent' => $g['plcehldr_userAgent'],
        'fbp' => $g['plcehldr_fbpCookie']
    ];

    // Advanced Matching fields (Meta format: string values)
    $meta_am_map = [
        'plcehldr_hashedEmail'   => 'em',
        'plcehldr_hashedPhone'   => 'ph',
        'plcehldr_hashedFn'      => 'fn',
        'plcehldr_hashedLn'      => 'ln',
        'plcehldr_hashedCt'      => 'ct',
        'plcehldr_hashedSt'      => 'st',
        'plcehldr_hashedZp'      => 'zp',
        'plcehldr_hashedCountry' => 'country',
    ];
    foreach ($meta_am_map as $placeholder => $apiKey) {
        if (!empty($g[$placeholder])) {
            $user_data[$apiKey] = $g[$placeholder];
        }
    }

    // Only tack on fbc if it's non‑empty
    if (strlen($fbc_value) > 5) {
        $user_data['fbc'] = $fbc_value;
    }

    $custom_data = [
        'currency'       => $g['plcehldr_currency'],
        'value'          => $g['plcehldr_value'],
        'transaction_id' => $g['plcehldr_transactionId'],
        'tax'            => $g['plcehldr_tax'],
        'shipping'       => $g['plcehldr_shipping'],
        'contents'       => array_map(function ($item) {
            return [
                'id'       => $item['plcehldr_itemId'],
                'quantity' => $item['plcehldr_quantity'],
                'item_price'    => $item['plcehldr_price'],
                //'item_name'     => $item['plcehldr_itemName']   ?? '' failing
            ];
        }, $g['plcehldr_lineItems']),
        'content_type' => 'product',
        'content_ids' => array_map(function ($item) {
            return strval($item['plcehldr_itemId']);
        }, $g['plcehldr_lineItems']),
    ];

    // Add content_category from first line item if available (Meta accepts a single string)
    $firstItem = $g['plcehldr_lineItems'][0] ?? [];
    $arrCategories = $firstItem['plcehldr_categories'] ?? [];
    if (! empty($arrCategories)) {
        $custom_data['content_category'] = implode(' > ', $arrCategories);
    }

    return [
        'event_id'   => $g['plcehldr_eventId'],
        'user_data'  => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}


/**
 * Maps the generic placeholders to "user_data" + "custom_data" + "event_id"
 * specifically for Google's purchase event (Measurement Protocol).
 * (Or a direct 'gtag' format if you prefer.)
 */
function unipixel_prepare_common_to_platform_purchase_google(array $g)
{

    

    // Retrieve client_id from the _ga cookie; fallback value could be '0.0' if not found.
    $raw_ga = isset($_COOKIE['_ga']) ? sanitize_text_field($_COOKIE['_ga']) : '';
    $client_id = unipixel_normalize_ga_client_id($raw_ga); // returns "X.Y" or ''

    $user_data = [
        'client_id' => $client_id,
    ];

    // "custom_data" or "params"
    $custom_data = [
        'currency' => $g['plcehldr_currency'],
        'value'    => $g['plcehldr_value'],
        'transaction_id' => $g['plcehldr_transactionId'],
        'tax'      => $g['plcehldr_tax'],
        'shipping' => $g['plcehldr_shipping'],
        'items' => array_map(function ($item) {

            $googleItem = [
                'item_id'        => $item['plcehldr_itemId'],
                'item_name'      => $item['plcehldr_itemName'],
                'quantity'       => $item['plcehldr_quantity'],
                'price'          => $item['plcehldr_price'],
            ];

            // Add variant if present
            $variant = $item['plcehldr_variant'] ?? '';
            if ($variant !== '') {
                $googleItem['item_variant'] = $variant;
            }

            // Map categories to item_category, item_category2 … item_category5 (GA4 spec)
            $arrCategories = $item['plcehldr_categories'] ?? [];
            foreach (array_slice($arrCategories, 0, 5) as $idx => $cat) {
                $key = ($idx === 0) ? 'item_category' : 'item_category' . ($idx + 1);
                $googleItem[$key] = $cat;
            }

            return $googleItem;
        }, $g['plcehldr_lineItems'])
    ];

    $gclid_value = unipixel_get_gclid_value();
    if (! empty($gclid_value)) {
        $custom_data['gclid'] = $gclid_value;
    }


    return [
        'event_id'   => $g['plcehldr_eventId'],
        'user_data'  => $user_data,    // e.g. client_id or user props
        'custom_data' => $custom_data,  // GA4 "params"
    ];
}



/**
 * Maps generic Purchase data to Pinterest's format (checkout).
 */
function unipixel_prepare_common_to_platform_purchase_pinterest(array $g)
{
    $pageUrl = unipixel_get_current_page_url();

    $user_data = [
        'client_ip_address' => $g['plcehldr_clientIp'],
        'client_user_agent' => $g['plcehldr_userAgent'],
    ];

    // Advanced Matching fields (Pinterest format: array-wrapped values)
    $pinterest_am_map = [
        'plcehldr_hashedEmail'   => 'em',
        'plcehldr_hashedPhone'   => 'ph',
        'plcehldr_hashedFn'      => 'fn',
        'plcehldr_hashedLn'      => 'ln',
        'plcehldr_hashedCt'      => 'ct',
        'plcehldr_hashedSt'      => 'st',
        'plcehldr_hashedZp'      => 'zp',
        'plcehldr_hashedCountry' => 'country',
    ];
    foreach ($pinterest_am_map as $placeholder => $apiKey) {
        if (!empty($g[$placeholder])) {
            $user_data[$apiKey] = [$g[$placeholder]];
        }
    }

    $epik_value = sanitize_text_field($_COOKIE['_epik'] ?? '');
    if (!empty($epik_value)) {
        $user_data['click_id'] = $epik_value;
    }

    $custom_data = [
        'currency'    => $g['plcehldr_currency'] ?? '',
        'value'       => (string) ($g['plcehldr_value'] ?? 0),
        'order_id'    => (string) ($g['plcehldr_transactionId'] ?? ''),
        'num_items'   => count($g['plcehldr_lineItems'] ?? []),
        'content_ids' => array_map(function ($item) {
            return (string) $item['plcehldr_itemId'];
        }, $g['plcehldr_lineItems'] ?? []),
        'contents'    => array_map(function ($item) {
            $contentItem = [
                'id'         => (string) ($item['plcehldr_itemId'] ?? ''),
                'item_name'  => $item['plcehldr_itemName'] ?? '',
                'item_price' => (string) ($item['plcehldr_price'] ?? 0),
                'quantity'   => (int) ($item['plcehldr_quantity'] ?? 1),
            ];

            $arrCategories = $item['plcehldr_categories'] ?? [];
            if (!empty($arrCategories)) {
                $contentItem['item_category'] = implode(' > ', $arrCategories);
            }

            return $contentItem;
        }, $g['plcehldr_lineItems'] ?? []),
    ];

    return [
        'event_id'    => $g['plcehldr_eventId'] ?? '',
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}


/**
 * Maps the generic placeholders to "user_data" + "properties" + "event_id"
 * specifically for TikTok's Events API (Purchase event).
 */
function unipixel_prepare_common_to_platform_purchase_tiktok(array $g)
{
    $pageUrl = unipixel_get_current_page_url();
    $ttvals  = unipixel_get_tt_value(); // returns ['ttclid'=>..., 'ttp_cookie'=>...]

    // --- Build user_data ---
    $user_data = [
        'ip'         => $g['plcehldr_clientIp']  ?? unipixel_get_ip_address(),
        'user_agent' => $g['plcehldr_userAgent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'ttp'        => $ttvals['ttp_cookie']    ?? '',
        'ttclid'     => $ttvals['ttclid']        ?? '',
    ];

    // Advanced Matching fields (TikTok format: string values)
    $tiktok_am_map = [
        'plcehldr_hashedEmail'   => 'email',
        'plcehldr_hashedPhone'   => 'phone',
        'plcehldr_hashedFn'      => 'first_name',
        'plcehldr_hashedLn'      => 'last_name',
        'plcehldr_hashedCt'      => 'city',
        'plcehldr_hashedSt'      => 'state',
        'plcehldr_hashedZp'      => 'zip_code',
        'plcehldr_hashedCountry' => 'country',
    ];
    foreach ($tiktok_am_map as $placeholder => $apiKey) {
        if (!empty($g[$placeholder])) {
            $user_data[$apiKey] = $g[$placeholder];
        }
    }

    // --- Build properties (TikTok's equivalent of custom_data) ---
    $properties = [
        'currency'       => $g['plcehldr_currency']       ?? '',
        'value'          => (float)($g['plcehldr_value']  ?? 0),
        'transaction_id' => (string)($g['plcehldr_transactionId'] ?? ''),
        'tax'            => (float)($g['plcehldr_tax']    ?? 0),
        'shipping'       => (float)($g['plcehldr_shipping'] ?? 0),
        'contents'       => array_map(function ($item) {
            $contentItem = [
                'content_id'   => (string)($item['plcehldr_itemId']   ?? ''),
                'content_name' => $item['plcehldr_itemName']         ?? '',
                'content_type' => 'product',
                'quantity'     => (int)($item['plcehldr_quantity']   ?? 1),
                'price'        => (float)($item['plcehldr_price']    ?? 0),
            ];

            // Add content_category if available
            $arrCategories = $item['plcehldr_categories'] ?? [];
            if (! empty($arrCategories)) {
                $contentItem['content_category'] = implode(' > ', $arrCategories);
            }

            return $contentItem;
        }, $g['plcehldr_lineItems'] ?? []),
    ];

    return [
        'event_id'   => $g['plcehldr_eventId'] ?? '',
        'user_data'  => $user_data,
        'custom_data' => $properties,
        'pageUrl'    => $pageUrl,
    ];
}


/**
 * Maps generic Purchase data to Microsoft's format (UET CAPI).
 */
function unipixel_prepare_common_to_platform_purchase_microsoft(array $g)
{
    $pageUrl = unipixel_get_current_page_url();
    $msclkid = unipixel_get_msclkid_value();

    // --- Build user_data ---
    $user_data = [
        'clientUserAgent'  => $g['plcehldr_userAgent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'clientIpAddress'  => $g['plcehldr_clientIp']  ?? unipixel_get_ip_address(),
    ];

    if (!empty($msclkid)) {
        $user_data['msclkid'] = $msclkid;
    }

    // Advanced Matching fields (Microsoft format: plain strings like Meta/TikTok)
    $ms_am_map = [
        'plcehldr_hashedEmail' => 'em',
        'plcehldr_hashedPhone' => 'ph',
    ];
    foreach ($ms_am_map as $placeholder => $apiKey) {
        if (!empty($g[$placeholder])) {
            $user_data[$apiKey] = $g[$placeholder];
        }
    }

    // --- Build custom_data ---
    $custom_data = [
        'pageType'        => 'purchase',
        'value'           => $g['plcehldr_value'] ?? 0,
        'currency'        => $g['plcehldr_currency'] ?? '',
        'transactionId'   => (string) ($g['plcehldr_transactionId'] ?? ''),
        'ecommTotalValue' => $g['plcehldr_value'] ?? 0,
        'items'           => array_map(function ($item) {
            return [
                'id'       => (string) ($item['plcehldr_itemId'] ?? ''),
                'name'     => $item['plcehldr_itemName'] ?? '',
                'quantity' => (int) ($item['plcehldr_quantity'] ?? 1),
                'price'    => (float) ($item['plcehldr_price'] ?? 0),
            ];
        }, $g['plcehldr_lineItems'] ?? []),
        'itemIds'         => array_map(function ($item) {
            return (string) ($item['plcehldr_itemId'] ?? '');
        }, $g['plcehldr_lineItems'] ?? []),
    ];

    return [
        'event_id'    => $g['plcehldr_eventId'] ?? '',
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}

