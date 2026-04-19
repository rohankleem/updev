<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\hook-handlers-viewcontent.php

add_action('woocommerce_loaded', 'prefix_woocommerce_loaded_viewcontent');

function prefix_woocommerce_loaded_viewcontent()
{
	add_action('woocommerce_before_single_product', 'unipixel_woocommerce_handler_viewcontent', 10);
}

function unipixel_woocommerce_handler_viewcontent()
{

	if (! class_exists('WooCommerce')) {
		return;
	}

	// Prevent bots or crawlers
	if (unipixel_denyCrawlerBots()) {
		return;
	}


	global $product;

	if (!is_object($product)) {
		return;
	}

	$product_id = $product->get_id();

	if (!$product_id) return;

	$platformIdMeta = 1;
	$eventNameMeta = "ViewContent";

	$platformIdPinterest = 2;
	$eventNamePinterest  = 'view_content';

	$platformIdTiktok = 3;
	$eventNameTiktok  = 'ViewContent';

	$platformIdGoogle = 4;
	$eventNameGoogle = "view_item";

	$platformIdMicrosoft = 5;
	$eventNameMicrosoft  = "view_item";

	$elementRef = "WordPress Hook Function";
	$eventTrigger = "WooCommerce View Content Hook";

	$wooPlatformSettingsMeta = unipixel_get_platform_settings($platformIdMeta);
	$wooPlatformSettingsPinterest = unipixel_get_platform_settings($platformIdPinterest);
	$wooPlatformSettingsTiktok = unipixel_get_platform_settings($platformIdTiktok);
	$wooPlatformSettingsGoogle = unipixel_get_platform_settings($platformIdGoogle);

	$wooEventSettingsMeta = unipixel_woo_event_get_settings($platformIdMeta, $eventNameMeta);
	$wooEventSettingsPinterest = unipixel_woo_event_get_settings($platformIdPinterest, $eventNamePinterest);
	$wooEventSettingsTiktok    = unipixel_woo_event_get_settings($platformIdTiktok, $eventNameTiktok);
	$wooEventSettingsGoogle = unipixel_woo_event_get_settings($platformIdGoogle, $eventNameGoogle);

	$wooPlatformSettingsMicrosoft = unipixel_get_platform_settings($platformIdMicrosoft);
	$wooEventSettingsMicrosoft    = unipixel_woo_event_get_settings($platformIdMicrosoft, $eventNameMicrosoft);

	$genericDataToSend = unipixel_get_common_woo_data_viewcontent($product_id);
	$eventTime = time();
	$eventTimeStampMs = (int) round(microtime(true) * 1000);


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

				$dataToSendMeta = unipixel_prepare_common_to_platform_viewcontent_meta($genericDataToSend);

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

				$mockResult = unipixel_build_client_result_mock(true,$genericDataToSend,$platformIdMeta,$eventNameMeta);
				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_viewcontent_for_meta($genericDataToSend, $eventTimeStampMs);
				unipixel_inline_script_meta_viewcontent();

				$mockResult = unipixel_build_client_result_mock(false,$genericDataToSend,$platformIdMeta,$eventNameMeta);
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

				$dataToSendGoogle = unipixel_prepare_common_to_platform_viewcontent_google($genericDataToSend);

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

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend ,$platformIdGoogle, $eventNameGoogle);
				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_viewcontent_for_google($genericDataToSend);
				unipixel_inline_script_google_viewcontent();

				$mockResult = unipixel_build_client_result_mock(false,$genericDataToSend,$platformIdGoogle,$eventNameGoogle);
				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $mockResult, "client", "third", "clientSecond");
			}
		}
	}


	if ($wooPlatformSettingsTiktok->platform_enabled) {

	  if (!empty($wooPlatformSettingsTiktok->serverside_global_enabled)) {
		if ($wooEventSettingsTiktok->send_server) {

			if ($consentBlocked) {
				unipixel_handle_send_event_result($platformIdTiktok, $elementRef, $eventTrigger, $eventNameTiktok, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendTiktok = unipixel_prepare_common_to_platform_viewcontent_tiktok($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_tiktok(
					$eventNameTiktok,
					$dataToSendTiktok['user_data'],
					$dataToSendTiktok['custom_data'],
					$dataToSendTiktok['event_id'],
					$eventTime,
					$dataToSendTiktok['pageUrl'],
					!empty($wooEventSettingsTiktok->send_server_log_response),
					$consentAlreadyChecked
				);

				unipixel_handle_send_event_result($platformIdTiktok, $elementRef, $eventTrigger, $eventNameTiktok, $sendServerResultInfo, "server", "first", "serverFirst");

				unipixel_localize_console_logging_for_tiktok($eventNameTiktok, $sendServerResultInfo);
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsTiktok->send_client) {

			if ($consentBlocked) {
				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdTiktok, $eventNameTiktok);
				unipixel_handle_send_event_result($platformIdTiktok, $elementRef, $eventTrigger, $eventNameTiktok, $mockResult, "client", "third", "clientSecond");
			} else {

				unipixel_localize_viewcontent_for_tiktok($genericDataToSend);
				unipixel_inline_script_tiktok_viewcontent();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdTiktok, $eventNameTiktok);
				unipixel_handle_send_event_result($platformIdTiktok, $elementRef, $eventTrigger, $eventNameTiktok, $mockResult, "client", "third", "clientSecond");
			}
		}
	}


	// ── Pinterest (platform_id = 2) — API event name: "view_content" ──
	if ($wooPlatformSettingsPinterest->platform_enabled) {

	  if (!empty($wooPlatformSettingsPinterest->serverside_global_enabled)) {
		if ($wooEventSettingsPinterest->send_server) {

			if ($consentBlocked) {
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendPinterest = unipixel_prepare_common_to_platform_viewcontent_pinterest($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_pinterest(
					'view_content',
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

				unipixel_localize_viewcontent_for_pinterest($genericDataToSend);
				unipixel_inline_script_pinterest_viewcontent();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdPinterest, $eventNamePinterest);
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, "client", "third", "clientSecond");
			}
		}
	}


	// ── Microsoft (platform_id = 5) — CAPI event name: "view_item" ──
	if ($wooPlatformSettingsMicrosoft->platform_enabled) {

	  if (!empty($wooPlatformSettingsMicrosoft->serverside_global_enabled)) {
		if ($wooEventSettingsMicrosoft->send_server) {

			if ($consentBlocked) {
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendMicrosoft = unipixel_prepare_common_to_platform_viewcontent_microsoft($genericDataToSend);

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

				unipixel_localize_viewcontent_for_microsoft($genericDataToSend, $eventTimeStampMs);
				unipixel_inline_script_microsoft_viewcontent();

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdMicrosoft, $eventNameMicrosoft);
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, "client", "third", "clientSecond");
			}
		}
	}
}
