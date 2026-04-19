<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\hook-handlers-purchase.php

add_action('woocommerce_loaded', 'prefix_woocommerce_loaded_purchase');

function prefix_woocommerce_loaded_purchase()
{
	add_action('woocommerce_thankyou', 'unipixel_woocommerce_handler_purchase', 100, 1);
}



function unipixel_woocommerce_handler_purchase($order_id)
{

	if (! class_exists('WooCommerce')) {
		return;
	}

	if (!$order_id) return;

	// Prepare the generic placeholders
	$genericDataToSend = unipixel_get_common_woo_data_purchase($order_id);
	$eventTime = time();
	$eventTimeStampMs = (int) round(microtime(true) * 1000);

	if (!$genericDataToSend) return;


	$platformIdMeta = 1;
	$eventNameMeta = "Purchase";

	$platformIdPinterest = 2;
	$eventNamePinterest  = "checkout";

	$platformIdTikTok = 3;
	$eventNameTikTok  = "Purchase";

	$platformIdGoogle = 4;
	$eventNameGoogle = "purchase";

	$platformIdMicrosoft = 5;
	$eventNameMicrosoft  = "purchase";

	$elementRef = "WordPress Hook Function";
	$eventTrigger = "WooCommerce Purchase Hook";

	$wooPlatformSettingsMeta = unipixel_get_platform_settings($platformIdMeta);
	$wooPlatformSettingsPinterest = unipixel_get_platform_settings($platformIdPinterest);
	$wooPlatformSettingsGoogle = unipixel_get_platform_settings($platformIdGoogle);

	$wooEventSettingsMeta = unipixel_woo_event_get_settings($platformIdMeta, $eventNameMeta);
	$wooEventSettingsPinterest = unipixel_woo_event_get_settings($platformIdPinterest, $eventNamePinterest);
	$wooEventSettingsGoogle = unipixel_woo_event_get_settings($platformIdGoogle, $eventNameGoogle);

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

				$dataToSendMeta = unipixel_prepare_common_to_platform_purchase_meta($genericDataToSend);

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

				unipixel_localize_purchase_for_meta($genericDataToSend, $eventTimeStampMs);
				unipixel_inline_script_meta_purchase();

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

				$dataToSendGoogle = unipixel_prepare_common_to_platform_purchase_google($genericDataToSend);

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

				unipixel_localize_purchase_for_google($genericDataToSend);
				unipixel_inline_script_google_purchase();

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

				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendTikTok = unipixel_prepare_common_to_platform_purchase_tiktok($genericDataToSend);

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

				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $sendServerResultInfo, "server", "first", "serverFirst");

				unipixel_localize_console_logging_for_tiktok($eventNameTikTok, $sendServerResultInfo);
			}
		}
	  } // serverside_global_enabled

		// --- Client-side send ---
		if ($wooEventSettingsTikTok->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdTikTok, $eventNameTikTok);
				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $mockResult, 'client', 'third', 'clientSecond');
			} else {

				unipixel_localize_purchase_for_tiktok($genericDataToSend);
				unipixel_inline_script_tiktok_purchase();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdTikTok, $eventNameTikTok);
				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $mockResult, 'client', 'third', 'clientSecond');
			}
		}
	}


	// ── Pinterest (platform_id = 2) — API event name: "checkout" ──
	if ($wooPlatformSettingsPinterest->platform_enabled) {

	  if (!empty($wooPlatformSettingsPinterest->serverside_global_enabled)) {
		if ($wooEventSettingsPinterest->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendPinterest = unipixel_prepare_common_to_platform_purchase_pinterest($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_pinterest(
					'checkout',
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
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, 'client', 'third', 'clientSecond');
			} else {

				unipixel_localize_purchase_for_pinterest($genericDataToSend);
				unipixel_inline_script_pinterest_purchase();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdPinterest, $eventNamePinterest);
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, 'client', 'third', 'clientSecond');
			}
		}
	}


	// ── Microsoft (platform_id = 5) — CAPI event name: "purchase" ──
	if ($wooPlatformSettingsMicrosoft->platform_enabled) {

	  if (!empty($wooPlatformSettingsMicrosoft->serverside_global_enabled)) {
		if ($wooEventSettingsMicrosoft->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendMicrosoft = unipixel_prepare_common_to_platform_purchase_microsoft($genericDataToSend);

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
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, 'client', 'third', 'clientSecond');
			} else {

				unipixel_localize_purchase_for_microsoft($genericDataToSend, $eventTimeStampMs);
				unipixel_inline_script_microsoft_purchase();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdMicrosoft, $eventNameMicrosoft);
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, 'client', 'third', 'clientSecond');
			}
		}
	}
}
