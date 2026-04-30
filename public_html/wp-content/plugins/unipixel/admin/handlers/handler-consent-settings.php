<?php
/**
 * Handler for updating Consent Settings.
 */
add_action('wp_ajax_unipixel_update_consent_settings', 'unipixel_handle_consent_update');

function unipixel_handle_consent_update() {
	check_ajax_referer('unipixel_ajax_nonce', 'nonce');

	$consent_honour   = isset($_POST['consent_honour'])    ? intval($_POST['consent_honour'])    : 0;
	$consent_ui       = isset($_POST['consent_ui'])        ? sanitize_text_field($_POST['consent_ui']) : 'unipixel';
	$consent_ui_style = isset($_POST['consent_ui_style'])  ? intval($_POST['consent_ui_style'])  : 1;

	$consent_locale_override = isset($_POST['consent_locale_override'])
		? sanitize_text_field(wp_unslash($_POST['consent_locale_override']))
		: 'auto';
	if ($consent_locale_override !== 'auto' && !unipixel_consent_is_valid_locale($consent_locale_override)) {
		$consent_locale_override = 'auto';
	}

	$consent_show_reject = isset($_POST['consent_show_reject']) ? intval($_POST['consent_show_reject']) : 0;

	$consent_popup_style = isset($_POST['consent_popup_style'])
		? unipixel_consent_normalise_popup_style(sanitize_text_field(wp_unslash($_POST['consent_popup_style'])))
		: 'centred';

	// Force choice (dimmed overlay). Defaults to 1 (blocking) — strongest GDPR posture.
	$consent_force_choice = isset($_POST['consent_force_choice']) ? intval($_POST['consent_force_choice']) : 0;

	$newOptions = array(
		'consent_honour'           => $consent_honour,
		'consent_ui'               => $consent_ui,
		'consent_ui_style'         => $consent_ui_style,
		'consent_locale_override'  => $consent_locale_override,
		'consent_show_reject'      => $consent_show_reject,
		'consent_popup_style'      => $consent_popup_style,
		'consent_force_choice'     => $consent_force_choice,
	);

	$defaults = array(
		'consent_honour'           => 0,
		'consent_ui'               => 'unipixel',
		'consent_ui_style'         => 1,
		'consent_locale_override'  => 'auto',
		'consent_show_reject'      => 0,
		'consent_popup_style'      => 'centred',
		'consent_force_choice'     => 1,
	);

	$current_options = get_option('unipixel_consent_settings', $defaults);

	if ($current_options === $newOptions) {
		wp_send_json_success(['message' => 'No changes entered']);
		return;
	}

	$updated = update_option('unipixel_consent_settings', $newOptions);

	unipixel_metric_log(
		"Consent Settings Updated",
		"Consent Preferences",
		$newOptions
	);

	if ($updated) {
		wp_send_json_success(['message' => 'Consent settings updated successfully']);
	} else {
		wp_send_json_error(['message' => 'Failed to update consent settings']);
	}
}
