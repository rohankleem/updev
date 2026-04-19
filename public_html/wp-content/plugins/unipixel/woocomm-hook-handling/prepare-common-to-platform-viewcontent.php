<?php

// File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\prepare-common-to-platform-viewcontent.php

/**
 * Maps generic ViewContent data to Meta's format.
 *
 * @param array $g The generic placeholder data.
 * @return array   An associative array with keys: event_id, user_data, custom_data, pageUrl.
 */
function unipixel_prepare_common_to_platform_viewcontent_meta(array $g)
{
    $fbc_value = unipixel_get_fbc_value();
    $pageUrl = unipixel_get_current_page_url();

    $user_data = [
        'client_ip_address' => $g['plcehldr_clientIp'],
        'client_user_agent' => $g['plcehldr_userAgent'],
        'fbp'               => $g['plcehldr_fbpCookie']
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

    if (!empty($fbc_value) && strlen($fbc_value) > 5) {
        $user_data['fbc'] = $fbc_value;
    }

    $custom_data = [
        'content_ids'  => [strval($g['plcehldr_productId'])],
        'content_name' => $g['plcehldr_itemName'],
        'content_type' => 'product',
        'currency'     => $g['plcehldr_currency'],
        'value'        => (float)($g['plcehldr_price'] ?? 0),
    ];

    // Add content_category if available (Meta accepts a single string)
    $arrCategories = $g['plcehldr_categories'] ?? [];
    if (! empty($arrCategories)) {
        $custom_data['content_category'] = implode(' > ', $arrCategories);
    }

    return [
        'event_id'    => $g['plcehldr_eventId'],
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}


/**
 * Maps generic ViewContent data to Google's format.
 *
 * @param array $g The generic placeholder data.
 * @return array   An associative array with keys: event_id, user_data, and custom_data.
 */
function unipixel_prepare_common_to_platform_viewcontent_google(array $g)
{
    // Retrieve client_id from the _ga cookie; fallback value could be '0.0' if not found.
    $raw_ga = isset($_COOKIE['_ga']) ? sanitize_text_field($_COOKIE['_ga']) : '';
    $client_id = unipixel_normalize_ga_client_id($raw_ga); // returns "X.Y" or ''

    $user_data = [
        'client_id' => $client_id,
    ];

    $item = [
        'item_id'   => $g['plcehldr_productId'],
        'item_name' => $g['plcehldr_itemName'] ?? '',
        'price'     => $g['plcehldr_price'],
    ];

    // Add variant if present
    $variant = $g['plcehldr_variant'] ?? '';
    if ($variant !== '') {
        $item['item_variant'] = $variant;
    }

    // Map categories to item_category, item_category2 … item_category5 (GA4 spec)
    $arrCategories = $g['plcehldr_categories'] ?? [];
    foreach (array_slice($arrCategories, 0, 5) as $idx => $cat) {
        $key = ($idx === 0) ? 'item_category' : 'item_category' . ($idx + 1);
        $item[$key] = $cat;
    }

    $custom_data = [
        'items'    => [$item],
        'currency' => $g['plcehldr_currency'],
        'value'    => $g['plcehldr_price'],
    ];

    $gclid_value = unipixel_get_gclid_value();
    if (!empty($gclid_value)) {
        $custom_data['gclid'] = $gclid_value;
    }

    return [
        'event_id'    => $g['plcehldr_eventId'],
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
    ];
}



/**
 * Maps generic ViewContent data to TikTok's format.
 *
 * @param array $g The generic placeholder data.
 * @return array   An associative array with keys: event_id, user_data, properties, and pageUrl.
 */
/**
 * Maps generic ViewContent data to Pinterest's format (view_content).
 */
function unipixel_prepare_common_to_platform_viewcontent_pinterest(array $g)
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

    $contentItem = [
        'id'         => (string) ($g['plcehldr_productId'] ?? ''),
        'item_name'  => $g['plcehldr_itemName'] ?? '',
        'item_price' => (string) ($g['plcehldr_price'] ?? 0),
        'quantity'   => 1,
    ];

    $arrCategories = $g['plcehldr_categories'] ?? [];
    if (!empty($arrCategories)) {
        $contentItem['item_category'] = implode(' > ', $arrCategories);
    }

    $custom_data = [
        'currency'    => $g['plcehldr_currency'] ?? '',
        'value'       => (string) ($g['plcehldr_price'] ?? 0),
        'content_ids' => [(string) ($g['plcehldr_productId'] ?? '')],
        'contents'    => [$contentItem],
    ];

    return [
        'event_id'    => $g['plcehldr_eventId'] ?? '',
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}


function unipixel_prepare_common_to_platform_viewcontent_tiktok(array $g)
{
    $pageUrl = unipixel_get_current_page_url();
    $ttvals  = unipixel_get_tt_value(); // returns ['ttp_cookie'=>..., 'ttclid'=>...]

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

    $contentItem = [
        'content_id'   => (string)($g['plcehldr_productId'] ?? ''),
        'content_type' => 'product',
        'content_name' => $g['plcehldr_itemName'] ?? '',
        'quantity'     => (int)($g['plcehldr_quantity'] ?? 1),
        'price'        => (float)($g['plcehldr_price'] ?? 0),
    ];

    // Add content_category if available (TikTok accepts a single string per content item)
    $arrCategories = $g['plcehldr_categories'] ?? [];
    if (! empty($arrCategories)) {
        $contentItem['content_category'] = implode(' > ', $arrCategories);
    }

    $properties = [
        'value'    => (float)($g['plcehldr_price'] ?? 0),
        'currency' => $g['plcehldr_currency'] ?? '',
        'contents' => [$contentItem],
    ];

    return [
        'event_id'   => $g['plcehldr_eventId'] ?? '',
        'user_data'  => $user_data,
        'custom_data' => $properties,
        'pageUrl'    => $pageUrl,
    ];
}


/**
 * Maps generic ViewContent data to Microsoft's format (UET CAPI).
 */
function unipixel_prepare_common_to_platform_viewcontent_microsoft(array $g)
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
        'pageType'        => 'product',
        'value'           => (float) ($g['plcehldr_price'] ?? 0),
        'currency'        => $g['plcehldr_currency'] ?? '',
        'ecommTotalValue' => (float) ($g['plcehldr_price'] ?? 0),
        'items'           => [
            [
                'id'       => (string) ($g['plcehldr_productId'] ?? ''),
                'name'     => $g['plcehldr_itemName'] ?? '',
                'quantity' => 1,
                'price'    => (float) ($g['plcehldr_price'] ?? 0),
            ],
        ],
        'itemIds'         => [(string) ($g['plcehldr_productId'] ?? '')],
    ];

    return [
        'event_id'    => $g['plcehldr_eventId'] ?? '',
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}
