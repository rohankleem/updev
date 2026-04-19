<?php

//File: public_html\wp-content\plugins\unipixel\functions\consent.php

function unipixel_get_consent_summary()
{

	$summary = [

		'necessary'   => true, // Core cookies essential to website functionality (often allowed by default)
		'functional'  => null, // Also known as: 'preferences', 'functional', 'functionality', 'essential' (depending on vendor)
		'performance' => null, // Also known as: 'statistics', 'analytics', 'performance', 'measurement'
		'marketing'   => null  // Also known as: 'marketing', 'advertising', 'ads', 'targeting'
	];


	// UniPixel's own summary (always takes precedence if set)
	if (isset($_COOKIE['unipixel_consent_summary'])) {
		$parsed = json_decode(stripslashes($_COOKIE['unipixel_consent_summary']), true);
		if (is_array($parsed)) {
			$summary = array_merge($summary, $parsed);
		}
	}

	// OneTrust
	if (isset($_COOKIE['OptanonConsent'])) {
		$parsed = unipixel_parse_onetrust_cookie();
		$summary = array_merge($summary, $parsed);
	}

	// Silktide
	if (isset($_COOKIE['CookieControl'])) {
		$parsed = unipixel_parse_silktide_cookie();
		$summary = array_merge($summary, $parsed);
	}

	// Cookiebot
	if (isset($_COOKIE['CookieConsent'])) {
		$parsed = unipixel_parse_cookiebot_cookie($_COOKIE['CookieConsent']);
		$summary = array_merge($summary, $parsed);
	}

	// Osano
	if (isset($_COOKIE['osano_consentmanager'])) {
		$parsed = unipixel_parse_osano_cookie();
		$summary = array_merge($summary, $parsed);
	}

	// Orest Bida CMP
	if (isset($_COOKIE['cookie_consent'])) {
		$parsed = unipixel_parse_orestbida_cookie();
		$summary = array_merge($summary, $parsed);
	}

	// Complianz
	if (isset($_COOKIE['cmplz_marketing']) || isset($_COOKIE['cmplz_statistics']) || isset($_COOKIE['cmplz_functional']) || isset($_COOKIE['cmplz_preferences'])) {
		$parsed = unipixel_parse_complianz_cookies();
		$summary = array_merge($summary, $parsed);
	}

	// CookieYes
	if (isset($_COOKIE['cookieyes-consent'])) {
		$parsed = unipixel_parse_cookieyes_cookie();
		$summary = array_merge($summary, $parsed);
	}

	// Moove GDPR
	if (isset($_COOKIE['moove_gdpr_popup'])) {
		$parsed = unipixel_parse_moove_gdpr_cookie();
		$summary = array_merge($summary, $parsed);
	}


	return $summary;
}


function unipixel_parse_onetrust_cookie()
{
	if (empty($_COOKIE['OptanonConsent'])) {
		return [];
	}

	$cookie = sanitize_text_field(wp_unslash($_COOKIE['OptanonConsent']));
	$parsed = [];

	// Look for segments like "C0001:1,C0002:0"
	if (preg_match('/groups=([^;]+)/', $cookie, $matches)) {
		$groupPairs = explode(',', $matches[1]);
		foreach ($groupPairs as $pair) {
			list($group, $value) = explode(':', $pair);
			$parsed[trim($group)] = $value === '1';
		}
	}

	return $parsed;
}


function unipixel_parse_silktide_cookie()
{
	if (empty($_COOKIE['CookieControl'])) {
		return [];
	}

	$cookie = json_decode(stripslashes($_COOKIE['CookieControl']), true);
	if (!is_array($cookie)) {
		return [];
	}

	$parsed = [];
	if (!empty($cookie['categories'])) {
		foreach ($cookie['categories'] as $category => $value) {
			$parsed[$category] = (bool) $value;
		}
	}

	return $parsed;
}


function unipixel_parse_cookiebot_cookie($cookieValue)
{
	$result = array(
		'necessary'   => false,
		'performance' => false,
		'marketing'   => false
	);

	if (empty($cookieValue)) {
		return $result;
	}

	$decoded = json_decode($cookieValue, true);

	if (is_array($decoded)) {
		$result['necessary']   = !empty($decoded['necessary']);
		$result['performance'] = !empty($decoded['statistics']) || !empty($decoded['preferences']);
		$result['marketing']   = !empty($decoded['marketing']);
	}

	return $result;
}



function unipixel_parse_osano_cookie()
{
	if (empty($_COOKIE['osano_consentmanager'])) {
		return [];
	}

	$cookie = json_decode(stripslashes($_COOKIE['osano_consentmanager']), true);
	if (!is_array($cookie) || empty($cookie['consent']) || !is_array($cookie['consent'])) {
		return [];
	}

	return array_map('boolval', $cookie['consent']);
}


function unipixel_parse_orestbida_cookie()
{
    // Support both possible cookie names
    $cookieValue = $_COOKIE['cc_cookie'] ?? $_COOKIE['cookie_consent'] ?? null;
    if (empty($cookieValue)) {
        return [];
    }

    $cookieValue = sanitize_text_field(wp_unslash($cookieValue));
    $decoded = json_decode($cookieValue, true);

    if (!is_array($decoded)) {
        return [];
    }

    // Handle both possible keys: 'categories' or 'acceptedCategories'
    $accepted = $decoded['categories'] ?? $decoded['acceptedCategories'] ?? [];

    if (empty($accepted) || !is_array($accepted)) {
        return [];
    }

    return [
        'necessary'   => in_array('necessary', $accepted, true),
        'functional'  => in_array('functional', $accepted, true) || in_array('preferences', $accepted, true),
        'performance' => in_array('performance', $accepted, true) || in_array('analytics', $accepted, true) || in_array('statistics', $accepted, true),
        'marketing'   => in_array('marketing', $accepted, true) || in_array('advertising', $accepted, true),
    ];
}


function unipixel_parse_complianz_cookies()
{
	$result = array(
		'necessary'   => true,
		'functional'  => null,
		'performance' => null,
		'marketing'   => null,
	);

	// Complianz uses individual cookies per category with values 'allow' or 'deny'
	if (isset($_COOKIE['cmplz_marketing'])) {
		$val = sanitize_text_field(wp_unslash($_COOKIE['cmplz_marketing']));
		$result['marketing'] = ($val === 'allow');
	}

	if (isset($_COOKIE['cmplz_statistics'])) {
		$val = sanitize_text_field(wp_unslash($_COOKIE['cmplz_statistics']));
		$result['performance'] = ($val === 'allow');
	}

	if (isset($_COOKIE['cmplz_functional']) || isset($_COOKIE['cmplz_preferences'])) {
		$func = isset($_COOKIE['cmplz_functional'])
			? sanitize_text_field(wp_unslash($_COOKIE['cmplz_functional']))
			: '';
		$pref = isset($_COOKIE['cmplz_preferences'])
			? sanitize_text_field(wp_unslash($_COOKIE['cmplz_preferences']))
			: '';
		$result['functional'] = ($func === 'allow' || $pref === 'allow');
	}

	return $result;
}


function unipixel_parse_cookieyes_cookie()
{
	if (empty($_COOKIE['cookieyes-consent'])) {
		return array();
	}

	$cookie = sanitize_text_field(wp_unslash($_COOKIE['cookieyes-consent']));

	// Format: comma-separated key:value pairs
	// e.g. "consentid:xxx,consent:yes,necessary:yes,functional:yes,analytics:yes,performance:yes,advertisement:yes"
	$pairs = explode(',', $cookie);
	$parsed = array();
	foreach ($pairs as $pair) {
		$parts = explode(':', $pair, 2);
		if (count($parts) === 2) {
			$parsed[trim($parts[0])] = trim($parts[1]);
		}
	}

	return array(
		'necessary'   => true,
		'functional'  => isset($parsed['functional']) && $parsed['functional'] === 'yes',
		'performance' => (isset($parsed['analytics']) && $parsed['analytics'] === 'yes')
			|| (isset($parsed['performance']) && $parsed['performance'] === 'yes'),
		'marketing'   => isset($parsed['advertisement']) && $parsed['advertisement'] === 'yes',
	);
}


function unipixel_parse_moove_gdpr_cookie()
{
	if (empty($_COOKIE['moove_gdpr_popup'])) {
		return array();
	}

	$cookie = sanitize_text_field(wp_unslash($_COOKIE['moove_gdpr_popup']));

	// Format: comma-separated key:value pairs
	// e.g. "strictly:1,thirdparty:0,advanced:0"
	$pairs = explode(',', $cookie);
	$parsed = array();
	foreach ($pairs as $pair) {
		$parts = explode(':', $pair, 2);
		if (count($parts) === 2) {
			$parsed[trim($parts[0])] = trim($parts[1]);
		}
	}

	return array(
		'necessary'   => true,
		'marketing'   => isset($parsed['thirdparty']) && $parsed['thirdparty'] === '1',
		'performance' => isset($parsed['advanced']) && $parsed['advanced'] === '1',
	);
}



//function used by server side sending to prevent server side if consent honouring is opted in for
function unipixel_check_for_consent()
{

	//is consent to be honoured
	$consentOptionSettingsArr = get_option('unipixel_consent_settings', []);
	$honourConsent = isset($consentOptionSettingsArr['consent_honour']) && $consentOptionSettingsArr['consent_honour'];

	if ($honourConsent) {
		$unipixelConsentSummaryArr = unipixel_get_consent_summary();

		//null equates to not allowed, no explicit consent provided
		$isMarketingAllowed = isset($unipixelConsentSummaryArr['marketing']) ? $unipixelConsentSummaryArr['marketing'] : false;
		$isPerformanceAllowed = isset($unipixelConsentSummaryArr['performance']) ? $unipixelConsentSummaryArr['performance'] : false;

		////null equates to allowed - disabled
		// $isMarketingAllowed = ! (isset($unipixelConsentSummaryArr['marketing']) && $unipixelConsentSummaryArr['marketing'] === false);
		// $isPerformanceAllowed = ! (isset($unipixelConsentSummaryArr['performance']) && $unipixelConsentSummaryArr['performance'] === false);

		// Meta events classified as fall under marketing and performance
		if (!$isMarketingAllowed or !$isPerformanceAllowed) {
			$response["response"]["code"] = 1; //indicates blocked by non-consent
			$response["response"]["message"] = "";
			return ['response' => $response]; //stop and return
		}
	}

	// Consent check passed or not required — continue
	return false;
}
