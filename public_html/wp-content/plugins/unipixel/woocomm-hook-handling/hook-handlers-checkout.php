<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\hook-handlers-checkout.php

add_action('woocommerce_loaded', 'prefix_woocommerce_loaded_checkout');

function prefix_woocommerce_loaded_checkout()
{
	//add_action('woocommerce_ajax_added_to_cart', 'unipixel_woocommerce_add_to_cart_handler', 100, 2); temp removal
	add_action('wp_enqueue_scripts', 'unipixel_trigger_initiate_checkout_on_page_load', 100);
}


//add_action('woocommerce_before_checkout_form', 'unipixel_woocommerce_initiate_checkout_handler', 10, 1);

// add_action('wp_enqueue_scripts', 'unipixel_trigger_initiate_checkout_on_page_load',90);
// function unipixel_trigger_initiate_checkout_on_page_load() {
//     // Only run on checkout page, but not on the thank-you page.
//     if ( is_checkout() && ! is_order_received_page() ) {
// 		unipixel_woocommerce_initiate_checkout_handler();
//     }
// }

//manually trigger checkout as woocommerce_before_checkout_form unreliable in block checkout method

function unipixel_trigger_initiate_checkout_on_page_load()
{

	if (! class_exists('WooCommerce')) {
		return;
	}

	// Only run on checkout page, but not on the order received/thank-you page.
	if (! is_checkout() || is_order_received_page()) {
		return;
	}

	// Dedup: only fire once per cart state per session.
	// Re-fires if the cart changes (new checkout intent), but not on page
	// refresh, payment failure redirect, or back-button navigation.
	if (WC()->cart && WC()->session) {
		$currentCartHash = WC()->cart->get_cart_hash();
		$firedCartHash   = WC()->session->get('unipixel_checkout_fired_cart_hash');

		if ($firedCartHash === $currentCartHash) {
			return;
		}

		WC()->session->set('unipixel_checkout_fired_cart_hash', $currentCartHash);
	}

	// Now trigger main checkout handler
	unipixel_woocommerce_handler_checkout();
}


function unipixel_woocommerce_handler_checkout()
{
	// Retrieve generic checkout data (e.g. cart totals, tax, shipping, line items)
	$genericDataToSend = unipixel_get_common_woo_data_checkout();
	$eventTime = time();
	$eventTimeStampMs = (int) round(microtime(true) * 1000);

	if (!$genericDataToSend) {
		return;
	}

	$platformIdMeta = 1;
	$eventNameMeta = "InitiateCheckout";

	$platformIdPinterest = 2;
	$eventNamePinterest  = "initiate_checkout";

	$platformIdTikTok = 3;
	$eventNameTikTok  = "InitiateCheckout";

	$platformIdGoogle = 4;
	$eventNameGoogle = "begin_checkout";

	$platformIdMicrosoft = 5;
	$eventNameMicrosoft  = "begin_checkout";

	$elementRef = "WordPress Hook Function";
	$eventTrigger = "WooCommerce Visit Checkout Hook";

	$wooPlatformSettingsMeta 	= unipixel_get_platform_settings($platformIdMeta);
	$wooEventSettingsMeta 		= unipixel_woo_event_get_settings($platformIdMeta, $eventNameMeta);

	$wooPlatformSettingsPinterest = unipixel_get_platform_settings($platformIdPinterest);
	$wooEventSettingsPinterest    = unipixel_woo_event_get_settings($platformIdPinterest, $eventNamePinterest);

	$wooEventSettingsGoogle 	= unipixel_woo_event_get_settings($platformIdGoogle, $eventNameGoogle);
	$wooPlatformSettingsGoogle 	= unipixel_get_platform_settings($platformIdGoogle);

	$wooPlatformSettingsTikTok = unipixel_get_platform_settings($platformIdTikTok);
	$wooEventSettingsTikTok    = unipixel_woo_event_get_settings($platformIdTikTok, $eventNameTikTok);

	$wooPlatformSettingsMicrosoft = unipixel_get_platform_settings($platformIdMicrosoft);
	$wooEventSettingsMicrosoft    = unipixel_woo_event_get_settings($platformIdMicrosoft, $eventNameMicrosoft);


	$consentBlocked = unipixel_check_for_consent();
	//returns
	// $result["response"]["response"]["code"] === 1 or FALSE
	$consentAlreadyChecked = true;


	if ($wooPlatformSettingsMeta->platform_enabled) {

	  if (!empty($wooPlatformSettingsMeta->serverside_global_enabled)) {
		if ($wooEventSettingsMeta->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendMeta = unipixel_prepare_common_to_platform_checkout_meta($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_meta(
					$eventNameMeta,
					$dataToSendMeta['user_data'],
					$dataToSendMeta['custom_data'],
					$dataToSendMeta['event_id'],
					$eventTime,
					$dataToSendMeta['pageUrl'],
					!empty($wooEventSettingsMeta->send_server_log_response),
					$consentAlreadyChecked // consent also kept in the send but not required in tihs case
				);

				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $sendServerResultInfo, "server", "first", "serverFirst");

				// localize + inline script to mirror server-side in browser
				unipixel_localize_console_logging_for_meta($eventNameMeta, $sendServerResultInfo);

			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsMeta->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdMeta, $eventNameMeta);
				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_initiate_checkout_for_meta($genericDataToSend, $eventTimeStampMs);
				unipixel_inline_script_meta_initiate_checkout();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdMeta, $eventNameMeta);
				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $mockResult, "client", "third", "clientSecond");
			}
		}
	}



	if ($wooPlatformSettingsGoogle->platform_enabled) {

	  if (!empty($wooPlatformSettingsGoogle->serverside_global_enabled)) {
		if ($wooEventSettingsGoogle->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $consentBlocked, "server", "first", "serverFirst");

			} else {

				$dataToSendGoogle = unipixel_prepare_common_to_platform_checkout_google($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_google(
					$eventNameGoogle,
					$dataToSendGoogle['user_data'],
					$dataToSendGoogle['custom_data'],
					$dataToSendGoogle['event_id'],
					!empty($wooEventSettingsGoogle->send_server_log_response)
				);

				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $sendServerResultInfo, "server", "first", "serverFirst");

				// localize + inline script to mirror server-side in browser
				unipixel_localize_console_logging_for_google($eventNameGoogle, $sendServerResultInfo);
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsGoogle->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdGoogle, $eventNameGoogle);
				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_initiate_checkout_for_google($genericDataToSend);
				unipixel_inline_script_google_initiate_checkout();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdGoogle, $eventNameGoogle);
				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $mockResult, "client", "third", "clientSecond");
			}
		}
	}



	if ($wooPlatformSettingsTikTok->platform_enabled) {

	  if (!empty($wooPlatformSettingsTikTok->serverside_global_enabled)) {
		// --- Server-side send ---
		if ($wooEventSettingsTikTok->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result(
					$platformIdTikTok,
					$elementRef,
					$eventTrigger,
					$eventNameTikTok,
					$consentBlocked,
					"server",
					"first",
					"serverFirst"
				);

			} else {

				$dataToSendTikTok = unipixel_prepare_common_to_platform_checkout_tiktok($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_tiktok(
					$eventNameTikTok,
					$dataToSendTikTok['user_data'],
					$dataToSendTikTok['custom_data'],
					$dataToSendTikTok['event_id'],
					$eventTime,
					$dataToSendTikTok['pageUrl'],
					!empty($wooEventSettingsTikTok->send_server_log_response),
					$consentAlreadyChecked
				);

				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $sendServerResultInfo,"server","first","serverFirst");

				unipixel_localize_console_logging_for_tiktok($eventNameTikTok, $sendServerResultInfo);
			}
		}
	  } // serverside_global_enabled

		// --- Client-side send ---
		if ($wooEventSettingsTikTok->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdTikTok, $eventNameTikTok);
				unipixel_handle_send_event_result($platformIdTikTok,$elementRef,$eventTrigger,$eventNameTikTok,$mockResult,"client","third","clientSecond");

			} else {

				unipixel_localize_initiate_checkout_for_tiktok($genericDataToSend);
				unipixel_inline_script_tiktok_initiate_checkout();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdTikTok, $eventNameTikTok);
				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $mockResult, 'client', 'third', 'clientSecond');

			}
		}
	}


	// ── Pinterest (platform_id = 2) — API event name: "initiate_checkout" ──
	if ($wooPlatformSettingsPinterest->platform_enabled) {

	  if (!empty($wooPlatformSettingsPinterest->serverside_global_enabled)) {
		if ($wooEventSettingsPinterest->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendPinterest = unipixel_prepare_common_to_platform_checkout_pinterest($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_pinterest(
					'initiate_checkout',
					$dataToSendPinterest['user_data'],
					$dataToSendPinterest['custom_data'],
					$dataToSendPinterest['event_id'],
					$eventTime,
					$dataToSendPinterest['pageUrl'],
					!empty($wooEventSettingsPinterest->send_server_log_response),
					$consentAlreadyChecked
				);

				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $sendServerResultInfo, "server", "first", "serverFirst");

				unipixel_localize_console_logging_for_pinterest($eventNamePinterest, $sendServerResultInfo);
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsPinterest->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdPinterest, $eventNamePinterest);
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_initiate_checkout_for_pinterest($genericDataToSend);
				unipixel_inline_script_pinterest_initiate_checkout();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdPinterest, $eventNamePinterest);
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, 'client', 'third', 'clientSecond');
			}
		}
	}


	// ── Microsoft (platform_id = 5) — CAPI event name: "begin_checkout" ──
	if ($wooPlatformSettingsMicrosoft->platform_enabled) {

	  if (!empty($wooPlatformSettingsMicrosoft->serverside_global_enabled)) {
		if ($wooEventSettingsMicrosoft->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendMicrosoft = unipixel_prepare_common_to_platform_checkout_microsoft($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_microsoft(
					$eventNameMicrosoft,
					$dataToSendMicrosoft['user_data'],
					$dataToSendMicrosoft['custom_data'],
					$dataToSendMicrosoft['event_id'],
					$eventTime,
					$dataToSendMicrosoft['pageUrl'],
					!empty($wooEventSettingsMicrosoft->send_server_log_response),
					$consentAlreadyChecked
				);

				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $sendServerResultInfo, "server", "first", "serverFirst");

				unipixel_localize_console_logging_for_microsoft($eventNameMicrosoft, $sendServerResultInfo);
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsMicrosoft->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdMicrosoft, $eventNameMicrosoft);
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_checkout_for_microsoft($genericDataToSend, $eventTimeStampMs);
				unipixel_inline_script_microsoft_checkout();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdMicrosoft, $eventNameMicrosoft);
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, 'client', 'third', 'clientSecond');
			}
		}
	}


}
