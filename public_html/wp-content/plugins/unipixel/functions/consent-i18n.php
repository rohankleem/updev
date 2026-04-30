<?php

//File: public_html\wp-content\plugins\unipixel\functions\consent-i18n.php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Allowed HTML for rich-text consent strings.
 * Single shared allowlist — used both on save (wp_kses) and on render (defence in depth).
 */
function unipixel_consent_allowed_html()
{
    return array(
        'a'      => array('href' => true, 'target' => true, 'rel' => true, 'title' => true),
        'strong' => array(),
        'em'     => array(),
        'br'     => array(),
    );
}

/**
 * Default consent popup strings.
 * Each entry: type ('short' = plain text, 'rich' = limited HTML) + default English text.
 * Wrapped in __() so translators can provide .po/.mo files via translate.wordpress.org.
 */
function unipixel_consent_string_defaults()
{
    return array(
        'title' => array(
            'type'    => 'short',
            'label'   => 'Popup title',
            'default' => __('Your Privacy Choices', 'unipixel'),
        ),
        'body' => array(
            'type'    => 'rich',
            'label'   => 'Popup body',
            'default' => __('This site uses cookies or similar technologies for technical purposes and, with your consent, for functionality, experience, measurement and marketing (personalized ads). You can choose which categories you are happy for us to use before continuing, or by clicking Accept.', 'unipixel'),
        ),
        'btn_accept' => array(
            'type'    => 'short',
            'label'   => 'Accept button',
            'default' => __('Accept all', 'unipixel'),
        ),
        'btn_adjust' => array(
            'type'    => 'short',
            'label'   => 'Adjust-preferences button',
            'default' => __('Adjust preferences', 'unipixel'),
        ),
        'btn_reject' => array(
            'type'    => 'short',
            'label'   => 'Reject-all button (only shown if enabled)',
            'default' => __('Reject all', 'unipixel'),
        ),
        'panel_title' => array(
            'type'    => 'short',
            'label'   => 'Preferences panel title',
            'default' => __('Manage Your Preferences', 'unipixel'),
        ),
        'panel_body' => array(
            'type'    => 'rich',
            'label'   => 'Preferences panel body',
            'default' => __('You can control which types of events are allowed to be sent from this site.', 'unipixel'),
        ),
        'cat_functional_label' => array(
            'type'    => 'short',
            'label'   => 'Functional category label',
            'default' => __('Functional cookies', 'unipixel'),
        ),
        'cat_functional_desc' => array(
            'type'    => 'rich',
            'label'   => 'Functional category description',
            'default' => __('used to keep your preferences saved (like this consent choice) and enable essential plugin functionality.', 'unipixel'),
        ),
        'cat_performance_label' => array(
            'type'    => 'short',
            'label'   => 'Performance category label',
            'default' => __('Performance cookies', 'unipixel'),
        ),
        'cat_performance_desc' => array(
            'type'    => 'rich',
            'label'   => 'Performance category description',
            'default' => __('allow anonymous analytics data for improving how conversion events (like <em>page_view</em> or <em>add_to_cart</em>) are tracked and measured.', 'unipixel'),
        ),
        'cat_marketing_label' => array(
            'type'    => 'short',
            'label'   => 'Marketing category label',
            'default' => __('Marketing cookies', 'unipixel'),
        ),
        'cat_marketing_desc' => array(
            'type'    => 'rich',
            'label'   => 'Marketing category description',
            'default' => __('enable tracking for advertising platforms like Meta, Google Ads, and TikTok, so that conversions can be reported back to those platforms.', 'unipixel'),
        ),
        'panel_footer' => array(
            'type'    => 'rich',
            'label'   => 'Preferences panel footer',
            'default' => __('<strong>Necessary cookies</strong> are always on and required for the site to function correctly. They do not include any marketing or analytics data.', 'unipixel'),
        ),
        'btn_cancel' => array(
            'type'    => 'short',
            'label'   => 'Cancel button',
            'default' => __('Cancel', 'unipixel'),
        ),
        'btn_save' => array(
            'type'    => 'short',
            'label'   => 'Save-preferences button',
            'default' => __('Save preferences', 'unipixel'),
        ),
    );
}

/**
 * Per-field length caps (in characters).
 */
function unipixel_consent_string_limits()
{
    return array(
        'title'                 => 120,
        'body'                  => 2000,
        'btn_accept'            => 60,
        'btn_adjust'            => 60,
        'btn_reject'            => 60,
        'panel_title'           => 120,
        'panel_body'            => 2000,
        'cat_functional_label'  => 80,
        'cat_functional_desc'   => 500,
        'cat_performance_label' => 80,
        'cat_performance_desc'  => 500,
        'cat_marketing_label'   => 80,
        'cat_marketing_desc'    => 500,
        'panel_footer'          => 500,
        'btn_cancel'            => 60,
        'btn_save'              => 60,
    );
}

/**
 * Sanitise a single consent string per its declared type and length cap.
 * Same function used on admin save and on frontend render.
 */
function unipixel_consent_sanitize_string($key, $value)
{
    $defaults = unipixel_consent_string_defaults();
    if (!isset($defaults[$key])) {
        return '';
    }

    $type = $defaults[$key]['type'];
    if ($type === 'short') {
        $clean = sanitize_text_field((string) $value);
    } else {
        $clean = wp_kses((string) $value, unipixel_consent_allowed_html());
    }

    $limits = unipixel_consent_string_limits();
    if (isset($limits[$key]) && $limits[$key] !== null) {
        if (function_exists('mb_substr')) {
            $clean = mb_substr($clean, 0, $limits[$key]);
        } else {
            $clean = substr($clean, 0, $limits[$key]);
        }
    }

    return $clean;
}

/**
 * Validate a locale code format and confirm WordPress knows about it.
 */
function unipixel_consent_is_valid_locale($locale)
{
    if (!is_string($locale) || $locale === '') {
        return false;
    }
    if (!preg_match('/^[a-zA-Z]{2,3}(_[A-Za-z0-9]{2,8})?$/', $locale)) {
        return false;
    }
    $available = unipixel_consent_available_locales();
    return isset($available[$locale]);
}

/**
 * Return the list of WP-supported locales for the language picker.
 * Always includes en_US. Other entries sourced from wp_get_available_translations().
 */
function unipixel_consent_available_locales()
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $locales = array('en_US' => 'English (United States)');

    if (!function_exists('wp_get_available_translations')) {
        $translation_install = ABSPATH . 'wp-admin/includes/translation-install.php';
        if (file_exists($translation_install)) {
            require_once $translation_install;
        }
    }

    if (function_exists('wp_get_available_translations')) {
        $translations = wp_get_available_translations();
        if (is_array($translations)) {
            foreach ($translations as $code => $info) {
                $english = isset($info['english_name']) ? $info['english_name'] : $code;
                $native  = isset($info['native_name']) ? $info['native_name'] : '';
                $label = $english;
                if ($native !== '' && $native !== $english) {
                    $label .= ' — ' . $native;
                }
                $label .= ' (' . $code . ')';
                $locales[$code] = $label;
            }
        }
    }

    $cache = $locales;
    return $cache;
}

/**
 * Get the list of locales the admin has added overrides for.
 */
function unipixel_consent_get_override_locales()
{
    $overrides = get_option('unipixel_consent_strings_i18n', array());
    if (!is_array($overrides)) {
        return array();
    }
    return array_keys($overrides);
}

/**
 * Get raw overrides for a specific locale (no defaults merged).
 */
function unipixel_consent_get_overrides_for_locale($locale)
{
    $overrides = get_option('unipixel_consent_strings_i18n', array());
    if (!is_array($overrides) || !isset($overrides[$locale]) || !is_array($overrides[$locale])) {
        return array();
    }
    return $overrides[$locale];
}

/**
 * Resolve which locale the popup should render for this request.
 * Order: admin force-locale > visitor's user locale > site locale > en_US.
 */
function unipixel_consent_resolve_locale()
{
    $settings = get_option('unipixel_consent_settings', array());
    $force = isset($settings['consent_locale_override']) ? (string) $settings['consent_locale_override'] : 'auto';

    if ($force !== 'auto' && $force !== '' && unipixel_consent_is_valid_locale($force)) {
        return $force;
    }

    if (function_exists('get_user_locale')) {
        $ulocale = get_user_locale();
        if ($ulocale) {
            return $ulocale;
        }
    }

    $site_locale = get_locale();
    return $site_locale ? $site_locale : 'en_US';
}

/**
 * Resolve the final strings for a locale.
 * Merge order: admin override > WP translation (.po/.mo default) > built-in default.
 * Every returned value passes through the sanitiser — defence in depth.
 */
function unipixel_consent_get_strings($locale = null)
{
    if ($locale === null) {
        $locale = unipixel_consent_resolve_locale();
    }

    // Read .mo translations directly (bypasses textdomain machinery, which has
    // quirks around runtime locale switching — this is predictable regardless
    // of WP version or registry state).
    $mo_translations = unipixel_consent_load_mo_translations($locale);

    $defaults = unipixel_consent_string_defaults();
    $locale_overrides = unipixel_consent_get_overrides_for_locale($locale);

    $resolved = array();
    foreach ($defaults as $key => $info) {
        $english = $info['default'];
        $value   = $english;

        // Apply .mo translation keyed by the English source (gettext style)
        if (isset($mo_translations[$english])) {
            $value = $mo_translations[$english];
        }

        // Admin override wins
        if (isset($locale_overrides[$key]) && $locale_overrides[$key] !== '') {
            $value = $locale_overrides[$key];
        }

        $resolved[$key] = unipixel_consent_sanitize_string($key, $value);
    }

    return $resolved;
}

/**
 * Load .mo translations for a locale as a flat map: english => translation.
 * Returns empty array if no .mo exists or parsing fails.
 */
function unipixel_consent_load_mo_translations($locale)
{
    static $cache = array();
    if (isset($cache[$locale])) {
        return $cache[$locale];
    }

    $result = array();

    if (!is_string($locale) || $locale === '' || $locale === 'en_US') {
        $cache[$locale] = $result;
        return $result;
    }

    // Check wp-content/languages/plugins/ first (persistent, update-safe),
    // then the plugin's own bundled languages/ folder.
    $candidates = array();
    if (defined('WP_LANG_DIR')) {
        $candidates[] = WP_LANG_DIR . '/plugins/unipixel-' . $locale . '.mo';
    }
    $candidates[] = dirname(dirname(__FILE__)) . '/languages/unipixel-' . $locale . '.mo';

    $mo_file = '';
    foreach ($candidates as $c) {
        if (file_exists($c)) {
            $mo_file = $c;
            break;
        }
    }

    if ($mo_file === '') {
        $cache[$locale] = $result;
        return $result;
    }

    // WP's POMO library — loaded on demand if not already present
    if (!class_exists('MO')) {
        if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-includes/pomo/mo.php')) {
            require_once ABSPATH . 'wp-includes/pomo/mo.php';
        }
    }

    if (class_exists('MO')) {
        $mo = new MO();
        if ($mo->import_from_file($mo_file)) {
            foreach ($mo->entries as $entry) {
                if (is_object($entry) && isset($entry->singular) && !empty($entry->translations)) {
                    $result[$entry->singular] = $entry->translations[0];
                }
            }
        }
    }

    $cache[$locale] = $result;
    return $result;
}

/**
 * List of available popup style presets.
 * Key = style id (used as CSS class suffix), value = admin-facing label + description.
 *
 * Note: the "blocking / force choice" behaviour (whether a dimmed overlay is shown
 * forcing the visitor to interact with the popup) is a SEPARATE setting from style.
 * Any style can be paired with either blocking or non-blocking behaviour.
 */
function unipixel_consent_get_popup_styles()
{
    return array(
        'centred' => array(
            'label'       => 'Centred card (default)',
            'description' => 'Floating card at the bottom-centre of the screen.',
        ),
        'bottom-bar' => array(
            'label'       => 'Bottom bar',
            'description' => 'Full-width strip stuck to the bottom of the screen.',
        ),
        'top-bar' => array(
            'label'       => 'Top bar',
            'description' => 'Full-width strip stuck to the top of the screen.',
        ),
        'bottom-left' => array(
            'label'       => 'Bottom-left corner',
            'description' => 'Small card anchored to the bottom-left.',
        ),
        'bottom-right' => array(
            'label'       => 'Bottom-right corner',
            'description' => 'Small card anchored to the bottom-right.',
        ),
    );
}

/**
 * Validate a style id; returns the id if known, else 'centred'.
 */
function unipixel_consent_normalise_popup_style($style)
{
    $styles = unipixel_consent_get_popup_styles();
    return (is_string($style) && isset($styles[$style])) ? $style : 'centred';
}

/**
 * Human-readable label for a locale code.
 */
function unipixel_consent_locale_label($locale)
{
    $available = unipixel_consent_available_locales();
    return isset($available[$locale]) ? $available[$locale] : $locale;
}

/**
 * List of locales that have a bundled .mo translation shipped with this plugin.
 * Scans the plugin's own languages/ folder at runtime — re-runs are cheap (static cache).
 */
function unipixel_consent_get_bundled_locales()
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $lang_dir = dirname(dirname(__FILE__)) . '/languages';
    $locales  = array();
    if (is_dir($lang_dir)) {
        $files = glob($lang_dir . '/unipixel-*.mo');
        if (is_array($files)) {
            foreach ($files as $file) {
                $basename = basename($file, '.mo');
                if (strpos($basename, 'unipixel-') === 0) {
                    $locale = substr($basename, strlen('unipixel-'));
                    if ($locale !== '') {
                        $locales[] = $locale;
                    }
                }
            }
        }
    }
    sort($locales);
    $cache = $locales;
    return $cache;
}

/**
 * Does a compiled .mo translation file exist for this locale?
 * Checks both wp-content/languages/plugins (network) and plugin's own languages/ folder.
 */
function unipixel_consent_has_translation_for_locale($locale)
{
    if (!is_string($locale) || $locale === '' || $locale === 'en_US') {
        return false;
    }
    $plugin_root = dirname(dirname(__FILE__));
    $plugin_mo   = $plugin_root . '/languages/unipixel-' . $locale . '.mo';
    if (file_exists($plugin_mo)) {
        return true;
    }
    if (defined('WP_LANG_DIR')) {
        $network_mo = WP_LANG_DIR . '/plugins/unipixel-' . $locale . '.mo';
        if (file_exists($network_mo)) {
            return true;
        }
    }
    return false;
}
