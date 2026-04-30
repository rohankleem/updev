<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handlers for consent popup localization CRUD.
 *
 * All handlers:
 * - Verify the same nonce used elsewhere in admin (unipixel_ajax_nonce).
 * - Check manage_options capability.
 * - Validate locale against the WP-known list before touching storage.
 * - Sanitise every string per its declared type via unipixel_consent_sanitize_string().
 */

add_action('wp_ajax_unipixel_consent_i18n_add_locale',    'unipixel_handle_consent_i18n_add_locale');
add_action('wp_ajax_unipixel_consent_i18n_save_locale',   'unipixel_handle_consent_i18n_save_locale');
add_action('wp_ajax_unipixel_consent_i18n_delete_locale', 'unipixel_handle_consent_i18n_delete_locale');


function unipixel_handle_consent_i18n_add_locale()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions.'));
    }

    $locale = isset($_POST['locale']) ? sanitize_text_field(wp_unslash($_POST['locale'])) : '';
    if (!unipixel_consent_is_valid_locale($locale)) {
        wp_send_json_error(array('message' => 'Unknown or invalid locale.'));
    }

    $overrides = get_option('unipixel_consent_strings_i18n', array());
    if (!is_array($overrides)) {
        $overrides = array();
    }

    if (isset($overrides[$locale])) {
        wp_send_json_error(array('message' => 'Language already added.'));
    }

    $overrides[$locale] = array();
    update_option('unipixel_consent_strings_i18n', $overrides);

    unipixel_metric_log(
        'Consent i18n — language added',
        'Consent Preferences',
        array('locale' => $locale)
    );

    wp_send_json_success(array(
        'locale' => $locale,
        'label'  => unipixel_consent_locale_label($locale),
    ));
}


function unipixel_handle_consent_i18n_save_locale()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions.'));
    }

    $locale = isset($_POST['locale']) ? sanitize_text_field(wp_unslash($_POST['locale'])) : '';
    if (!unipixel_consent_is_valid_locale($locale)) {
        wp_send_json_error(array('message' => 'Unknown or invalid locale.'));
    }

    $incoming = isset($_POST['strings']) && is_array($_POST['strings']) ? wp_unslash($_POST['strings']) : array();
    $defaults = unipixel_consent_string_defaults();

    $clean = array();
    foreach ($defaults as $key => $info) {
        if (!array_key_exists($key, $incoming)) {
            continue;
        }
        $raw = (string) $incoming[$key];
        // Empty input means "use the default" — do not store an empty override.
        if (trim($raw) === '') {
            continue;
        }
        $clean[$key] = unipixel_consent_sanitize_string($key, $raw);
    }

    $overrides = get_option('unipixel_consent_strings_i18n', array());
    if (!is_array($overrides)) {
        $overrides = array();
    }

    $overrides[$locale] = $clean;
    update_option('unipixel_consent_strings_i18n', $overrides);

    unipixel_metric_log(
        'Consent i18n — language saved',
        'Consent Preferences',
        array(
            'locale'     => $locale,
            'override_n' => count($clean),
        )
    );

    wp_send_json_success(array(
        'locale'  => $locale,
        'stored'  => $clean,
        'message' => 'Language saved.',
    ));
}


function unipixel_handle_consent_i18n_delete_locale()
{
    check_ajax_referer('unipixel_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions.'));
    }

    $locale = isset($_POST['locale']) ? sanitize_text_field(wp_unslash($_POST['locale'])) : '';
    if (!unipixel_consent_is_valid_locale($locale)) {
        wp_send_json_error(array('message' => 'Unknown or invalid locale.'));
    }

    $overrides = get_option('unipixel_consent_strings_i18n', array());
    if (!is_array($overrides) || !isset($overrides[$locale])) {
        wp_send_json_error(array('message' => 'Language not found.'));
    }

    unset($overrides[$locale]);
    update_option('unipixel_consent_strings_i18n', $overrides);

    unipixel_metric_log(
        'Consent i18n — language removed',
        'Consent Preferences',
        array('locale' => $locale)
    );

    wp_send_json_success(array('locale' => $locale));
}
