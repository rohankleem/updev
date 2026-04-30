<?php
/**
 * Diagnostic: bootstrap WP, simulate what the frontend does,
 * and print the strings that would be passed to the popup JS.
 *
 * Run: C:/xampp/php/php.exe _tools/check-consent-translation.php
 */

define('ABSPATH', dirname(__DIR__) . '/public_html/');
$_SERVER['HTTP_HOST'] = 'updev.local.site';
$_SERVER['REQUEST_URI'] = '/';

require_once dirname(__DIR__) . '/public_html/wp-load.php';

$locale_setting = get_option('unipixel_consent_settings', array());
echo "consent_locale_override in DB: " . (isset($locale_setting['consent_locale_override']) ? $locale_setting['consent_locale_override'] : '(unset)') . "\n\n";

$resolved = unipixel_consent_resolve_locale();
echo "Resolved locale: $resolved\n\n";

$mo_path = dirname(__DIR__) . '/public_html/wp-content/plugins/unipixel/languages/unipixel-' . $resolved . '.mo';
echo "MO file exists ($mo_path): " . (file_exists($mo_path) ? 'YES' : 'NO') . "\n\n";

$strings = unipixel_consent_get_strings();
echo "Strings returned by unipixel_consent_get_strings():\n";
foreach ($strings as $k => $v) {
    echo "  $k: $v\n";
}
