<?php

//File: public_html\wp-content\plugins\unipixel\woocomm-hook-handling\hook-handlers-addtocart.php

add_action('woocommerce_loaded', 'prefix_woocommerce_loaded_add_to_cart');

function prefix_woocommerce_loaded_add_to_cart()
{
	add_action('woocommerce_add_to_cart', 'unipixel_woocommerce_handler_add_to_cart', 100, 6);
}


/**
 * Handles the AddToCart event triggered by WooCommerce.
 * This function is hooked to woocommerce_add_to_cart and/or woocommerce_ajax_added_to_cart.
 *
 * @param string $cart_item_key The cart item key.
 * @param int    $product_id    The product ID.
 * @param int    $quantity      The quantity added.
 * @param int    $variation_id  (Optional) Variation ID.
 * @param array  $variation     (Optional) Variation data.
 * @param array  $cart_item_data (Optional) Additional cart item data.
 */
function unipixel_woocommerce_handler_add_to_cart(...$args)
{



	$triggeringHook = current_filter();

	if ($triggeringHook === 'woocommerce_add_to_cart') {
		if (count($args) < 6) {
			return; // Not enough parameters; do nothing.
		}
		list($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) = $args;
	} elseif ($triggeringHook === 'woocommerce_ajax_added_to_cart') {
		if (count($args) === 1) {
			// WooCommerce sometimes only passes the product ID.
			$product_id = $args[0];
			$quantity = 1;
			$cart_item_key = ''; // not available
		} elseif (count($args) >= 2) {
			list($cart_item_key, $product_id) = $args;
			$quantity = 1; // Default quantity
		} else {
			return; // Not enough data
		}
		$variation_id = 0;
		$variation = array();
		$cart_item_data = array();
	} else {
		return; // Unhandled hook.
	}


	$platformIdMeta = 1;
	$eventNameMeta = "AddToCart";

	$platformIdPinterest = 2;
	$eventNamePinterest = "add_to_cart";

	$platformIdTikTok = 3;
	$eventNameTikTok = "AddToCart";


	$platformIdGoogle = 4;
	$eventNameGoogle = "add_to_cart";

	$platformIdMicrosoft = 5;
	$eventNameMicrosoft  = "add_to_cart";

	$elementRef = "WordPress Hook Function";
	$eventTrigger = "WooCommerce Add To Cart Hook";

	$wooPlatformSettingsMeta = unipixel_get_platform_settings($platformIdMeta);
	$wooPlatformSettingsPinterest = unipixel_get_platform_settings($platformIdPinterest);
	$wooPlatformSettingsGoogle = unipixel_get_platform_settings($platformIdGoogle);

	$wooPlatformSettingsTikTok = unipixel_get_platform_settings($platformIdTikTok);
	$wooEventSettingsTikTok = unipixel_woo_event_get_settings($platformIdTikTok, $eventNameTikTok);

	$wooEventSettingsMeta = unipixel_woo_event_get_settings($platformIdMeta, $eventNameMeta);
	$wooEventSettingsPinterest = unipixel_woo_event_get_settings($platformIdPinterest, $eventNamePinterest);
	$wooEventSettingsGoogle = unipixel_woo_event_get_settings($platformIdGoogle, $eventNameGoogle);

	$wooPlatformSettingsMicrosoft = unipixel_get_platform_settings($platformIdMicrosoft);
	$wooEventSettingsMicrosoft    = unipixel_woo_event_get_settings($platformIdMicrosoft, $eventNameMicrosoft);

	// Use variation_id when present (customer selected a specific variation)
	$resolvedProductId = (!empty($variation_id)) ? $variation_id : $product_id;
	$genericDataToSend = unipixel_get_common_woo_data_addtocart($resolvedProductId, $quantity);
	$eventTime = time();
	$eventTimeStampMs = (int) round(microtime(true) * 1000);


	$isAjax = wp_doing_ajax() || (defined('DOING_AJAX') && DOING_AJAX);


	$consentBlocked = unipixel_check_for_consent();

	$consentAlreadyChecked = true;

	if ($wooPlatformSettingsMeta->platform_enabled) {

	  if (!empty($wooPlatformSettingsMeta->serverside_global_enabled)) {
		if ($wooEventSettingsMeta->send_server) {

			if ($consentBlocked) {

				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $consentBlocked, "server", "first", "serverFirst");
			} else {

				$dataToSendMeta = unipixel_prepare_common_to_platform_addtocart_meta($genericDataToSend);

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

				if ($isAjax) {
					UniPixel_AddToCart_Fragment_Collector::add_server_result('meta', $eventNameMeta, $sendServerResultInfo);
				} else {
					$key = 'unipixel_addtocart_server_meta_' . unipixel_get_user_identifier_for_transient();
					set_transient($key, $sendServerResultInfo, 60);
					unipixel_localize_console_logging_for_meta($eventNameMeta, $sendServerResultInfo);
				}
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsMeta->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend,  $platformIdMeta, $eventNameMeta);
				unipixel_handle_send_event_result($platformIdMeta, $elementRef, $eventTrigger, $eventNameMeta, $mockResult, "client", "third", "clientSecond");
			} else {

				if ($isAjax) {
					$clientData = unipixel_get_add_to_cart_client_data_meta($genericDataToSend, $eventTimeStampMs);
					UniPixel_AddToCart_Fragment_Collector::add_platform_data('meta', $clientData);
				} else {
					unipixel_localize_add_to_cart_for_meta($genericDataToSend, $eventTimeStampMs);
					unipixel_inline_script_meta_addtocart('woocommerce_add_to_cart', $genericDataToSend);
				}

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

				$dataToSendGoogle = unipixel_prepare_common_to_platform_addtocart_google($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_google(
					$eventNameGoogle,
					$dataToSendGoogle['user_data'],
					$dataToSendGoogle['custom_data'],
					$dataToSendGoogle['event_id'],
					!empty($wooEventSettingsGoogle->send_server_log_response)
				);

				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $sendServerResultInfo, "server", "first", "serverFirst");

				if ($isAjax) {
					UniPixel_AddToCart_Fragment_Collector::add_server_result('google', $eventNameGoogle, $sendServerResultInfo);
				} else {
					$key = 'unipixel_addtocart_server_google_' . unipixel_get_user_identifier_for_transient();
					set_transient($key, $sendServerResultInfo, 60);
					unipixel_localize_console_logging_for_google($eventNameGoogle, $sendServerResultInfo);
				}
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsGoogle->send_client) {

			if ($consentBlocked) {

				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend,  $platformIdGoogle, $eventNameGoogle);
				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $mockResult, "client", "third", "clientSecond");
			} else {

				if ($isAjax) {
					$clientData = unipixel_get_add_to_cart_client_data_google($genericDataToSend);
					UniPixel_AddToCart_Fragment_Collector::add_platform_data('google', $clientData);
				} else {
					unipixel_localize_add_to_cart_for_google($genericDataToSend);
					unipixel_inline_script_google_addtocart('woocommerce_add_to_cart', $genericDataToSend);
				}

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdGoogle, $eventNameGoogle);
				unipixel_handle_send_event_result($platformIdGoogle, $elementRef, $eventTrigger, $eventNameGoogle, $mockResult, "client", "third", "clientSecond");
			}
		}
	}




	if ($wooPlatformSettingsTikTok->platform_enabled) {

	  if (!empty($wooPlatformSettingsTikTok->serverside_global_enabled)) {
		// --- Server send ---
		if ($wooEventSettingsTikTok->send_server) {
			if ($consentBlocked) {
				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $consentBlocked, "server", "first", "serverFirst");
			} else {
				$dataToSendTikTok = unipixel_prepare_common_to_platform_addtocart_tiktok($genericDataToSend);

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

				if ($isAjax) {
					UniPixel_AddToCart_Fragment_Collector::add_server_result('tiktok', $eventNameTikTok, $sendServerResultInfo);
				} else {
					$key = 'unipixel_addtocart_server_tiktok_' . unipixel_get_user_identifier_for_transient();
					set_transient($key, $sendServerResultInfo, 60);
					unipixel_localize_console_logging_for_tiktok($eventNameTikTok, $sendServerResultInfo);
				}
			}
		}
	  } // serverside_global_enabled

		// --- Client send ---
		if ($wooEventSettingsTikTok->send_client) {
			if ($consentBlocked) {
				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdTikTok, $eventNameTikTok);
				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $mockResult, "client", "third", "clientSecond");
			} else {
				if ($isAjax) {
					$clientData = unipixel_get_add_to_cart_client_data_tiktok($genericDataToSend);
					UniPixel_AddToCart_Fragment_Collector::add_platform_data('tiktok', $clientData);
				} else {
					unipixel_localize_add_to_cart_for_tiktok($genericDataToSend);
					unipixel_inline_script_tiktok_addtocart('woocommerce_add_to_cart', $genericDataToSend);
				}


				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdTikTok, $eventNameTikTok);
				unipixel_handle_send_event_result($platformIdTikTok, $elementRef, $eventTrigger, $eventNameTikTok, $mockResult, "client", "third", "clientSecond");
			}
		}
	}


	// ── Pinterest (platform_id = 2) — API event name: "add_to_cart" ──
	if ($wooPlatformSettingsPinterest->platform_enabled) {

	  if (!empty($wooPlatformSettingsPinterest->serverside_global_enabled)) {
		if ($wooEventSettingsPinterest->send_server) {
			if ($consentBlocked) {
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $consentBlocked, "server", "first", "serverFirst");
			} else {
				$dataToSendPinterest = unipixel_prepare_common_to_platform_addtocart_pinterest($genericDataToSend);

				$sendServerResultInfo = unipixel_send_server_event_pinterest(
					'add_to_cart',
					$dataToSendPinterest['user_data'],
					$dataToSendPinterest['custom_data'],
					$dataToSendPinterest['event_id'],
					$eventTime,
					$dataToSendPinterest['pageUrl'],
					!empty($wooEventSettingsPinterest->send_server_log_response),
					$consentAlreadyChecked
				);

				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $sendServerResultInfo, "server", "first", "serverFirst");

				if ($isAjax) {
					UniPixel_AddToCart_Fragment_Collector::add_server_result('pinterest', $eventNamePinterest, $sendServerResultInfo);
				} else {
					$key = 'unipixel_addtocart_server_pinterest_' . unipixel_get_user_identifier_for_transient();
					set_transient($key, $sendServerResultInfo, 60);
					unipixel_localize_console_logging_for_pinterest($eventNamePinterest, $sendServerResultInfo);
				}
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsPinterest->send_client) {
			if ($consentBlocked) {
				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdPinterest, $eventNamePinterest);
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, "client", "third", "clientSecond");
			} else {
				if ($isAjax) {
					$clientData = unipixel_get_add_to_cart_client_data_pinterest($genericDataToSend);
					UniPixel_AddToCart_Fragment_Collector::add_platform_data('pinterest', $clientData);
				} else {
					unipixel_localize_add_to_cart_for_pinterest($genericDataToSend);
					unipixel_inline_script_pinterest_addtocart('woocommerce_add_to_cart', $genericDataToSend);
				}

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdPinterest, $eventNamePinterest);
				unipixel_handle_send_event_result($platformIdPinterest, $elementRef, $eventTrigger, $eventNamePinterest, $mockResult, "client", "third", "clientSecond");
			}
		}
	}


	// ── Microsoft (platform_id = 5) — CAPI event name: "add_to_cart" ──
	if ($wooPlatformSettingsMicrosoft->platform_enabled) {

	  if (!empty($wooPlatformSettingsMicrosoft->serverside_global_enabled)) {
		if ($wooEventSettingsMicrosoft->send_server) {
			if ($consentBlocked) {
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $consentBlocked, "server", "first", "serverFirst");
			} else {
				$dataToSendMicrosoft = unipixel_prepare_common_to_platform_addtocart_microsoft($genericDataToSend);

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

				if ($isAjax) {
					UniPixel_AddToCart_Fragment_Collector::add_server_result('microsoft', $eventNameMicrosoft, $sendServerResultInfo);
				} else {
					$key = 'unipixel_addtocart_server_microsoft_' . unipixel_get_user_identifier_for_transient();
					set_transient($key, $sendServerResultInfo, 60);
					unipixel_localize_console_logging_for_microsoft($eventNameMicrosoft, $sendServerResultInfo);
				}
			}
		}
	  } // serverside_global_enabled

		if ($wooEventSettingsMicrosoft->send_client) {
			if ($consentBlocked) {
				$mockResult = unipixel_build_client_result_mock(true, $genericDataToSend, $platformIdMicrosoft, $eventNameMicrosoft);
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, "client", "third", "clientSecond");
			} else {
				if ($isAjax) {
					$clientData = unipixel_get_add_to_cart_client_data_microsoft($genericDataToSend, $eventTimeStampMs);
					UniPixel_AddToCart_Fragment_Collector::add_platform_data('microsoft', $clientData);
				} else {
					unipixel_localize_add_to_cart_for_microsoft($genericDataToSend, $eventTimeStampMs);
					unipixel_inline_script_microsoft_addtocart('woocommerce_add_to_cart', $genericDataToSend);
				}

				$mockResult = unipixel_build_client_result_mock(false, $genericDataToSend, $platformIdMicrosoft, $eventNameMicrosoft);
				unipixel_handle_send_event_result($platformIdMicrosoft, $elementRef, $eventTrigger, $eventNameMicrosoft, $mockResult, "client", "third", "clientSecond");
			}
		}
	}
}
