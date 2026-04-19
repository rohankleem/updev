<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\prepare-common-to-platform-addtocart.php

/**
 * Maps generic AddToCart data to Meta's format.
 *
 * @param array $g The generic placeholder data.
 * @return array   An associative array with keys: event_id, user_data, and custom_data.
 */
function unipixel_prepare_common_to_platform_addtocart_meta(array $g)
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

    // Only tack on fbc if it's non‑empty
    if (strlen($fbc_value) > 5) {
        $user_data['fbc'] = $fbc_value;
    }

    // For an AddToCart event, we send the product ID and quantity as "contents"
    $custom_data = [
        'contents' => [
            [
                'id'       => $g['plcehldr_productId'],
                'quantity' => $g['plcehldr_quantity'],
                'price'    => $g['plcehldr_price'],
            ]
        ],
        'currency' => $g['plcehldr_currency'],
        'value'    => (float)($g['plcehldr_price'] * $g['plcehldr_quantity']),
        'content_type'  => 'product',
        'content_ids'   => [strval($g['plcehldr_productId'])],
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
 * Maps generic AddToCart data to Google's format.
 *
 * @param array $g The generic placeholder data.
 * @return array   An associative array with keys: event_id, user_data, and custom_data.
 */
function unipixel_prepare_common_to_platform_addtocart_google(array $g)
{

    // Retrieve client_id from the _ga cookie; fallback value could be '0.0' if not found.
    $raw_ga = isset($_COOKIE['_ga']) ? sanitize_text_field($_COOKIE['_ga']) : '';
    $client_id = unipixel_normalize_ga_client_id($raw_ga); // returns "X.Y" or ''

    $user_data = [
        'client_id' => $client_id,
    ];

    // Google typically expects an "items" array for eCommerce events.
    $item = [
        'item_id'   => $g['plcehldr_productId'],
        'item_name' => $g['plcehldr_itemName'] ?? '',
        'quantity'  => $g['plcehldr_quantity'],
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
        'value'    => (float)($g['plcehldr_price'] * $g['plcehldr_quantity']),
    ];

    $gclid_value = unipixel_get_gclid_value();
    if (! empty($gclid_value)) {
        $custom_data['gclid'] = $gclid_value;
    }


    return [
        'event_id'    => $g['plcehldr_eventId'],
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
    ];
}



/**
 * Maps generic AddToCart data to TikTok's format.
 *
 * @param array $g The generic placeholder data.
 * @return array   An associative array with keys: event_id, user_data, and properties.
 */
/**
 * Maps generic AddToCart data to Pinterest's format.
 */
function unipixel_prepare_common_to_platform_addtocart_pinterest(array $g)
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
        'id'         => (string) $g['plcehldr_productId'],
        'item_name'  => $g['plcehldr_itemName'] ?? '',
        'item_price' => (string) ($g['plcehldr_price'] ?? 0),
        'quantity'   => (int) ($g['plcehldr_quantity'] ?? 1),
    ];

    $arrCategories = $g['plcehldr_categories'] ?? [];
    if (!empty($arrCategories)) {
        $contentItem['item_category'] = implode(' > ', $arrCategories);
    }

    $custom_data = [
        'currency'    => $g['plcehldr_currency'] ?? '',
        'value'       => (string) (float) ($g['plcehldr_price'] * $g['plcehldr_quantity']),
        'num_items'   => 1,
        'content_ids' => [(string) $g['plcehldr_productId']],
        'contents'    => [$contentItem],
    ];

    return [
        'event_id'    => $g['plcehldr_eventId'],
        'user_data'   => $user_data,
        'custom_data' => $custom_data,
        'pageUrl'     => $pageUrl,
    ];
}


function unipixel_prepare_common_to_platform_addtocart_tiktok(array $g)
{
    $pageUrl = unipixel_get_current_page_url();
    $ttvals  = unipixel_get_tt_value(); // e.g. ['ttp_cookie'=>..., 'ttclid'=>...]

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
        'content_name' => $g['plcehldr_itemName'] ?? '',
        'content_type' => 'product',
        'quantity'     => (int)($g['plcehldr_quantity'] ?? 1),
        'price'        => (float)($g['plcehldr_price'] ?? 0),
    ];

    // Add content_category if available
    $arrCategories = $g['plcehldr_categories'] ?? [];
    if (! empty($arrCategories)) {
        $contentItem['content_category'] = implode(' > ', $arrCategories);
    }

    $properties = [
        'currency' => $g['plcehldr_currency'] ?? '',
        'value'    => (float)($g['plcehldr_price'] * $g['plcehldr_quantity']),
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
 * Maps generic AddToCart data to Microsoft's format (UET CAPI).
 */
function unipixel_prepare_common_to_platform_addtocart_microsoft(array $g)
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
    $itemValue = (float) (($g['plcehldr_price'] ?? 0) * ($g['plcehldr_quantity'] ?? 1));
    $value     = isset($g['plcehldr_cartTotal']) ? (float) $g['plcehldr_cartTotal'] : $itemValue;

    $custom_data = [
        'pageType'        => 'cart',
        'value'           => $value,
        'currency'        => $g['plcehldr_currency'] ?? '',
        'ecommTotalValue' => $value,
        'items'           => [
            [
                'id'       => (string) ($g['plcehldr_productId'] ?? ''),
                'name'     => $g['plcehldr_itemName'] ?? '',
                'quantity' => (int) ($g['plcehldr_quantity'] ?? 1),
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


/**
 * Hashes values per TikTok's requirements (SHA256, lowercase).
 * Used for personally identifiable user fields like email or phone.
 *
 * @param string $value
 * @return string
 */
function unipixel_hash_for_tiktok($value)
{
    $trimmed = trim(strtolower($value));
    return hash('sha256', $trimmed);
}
