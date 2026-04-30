<?php
// Smoke test for unipixel_url_pattern_match — exercises spec table from project doc.
// Run: php _tools/test-url-pattern-match.php
define('ABSPATH', __DIR__);
function wp_parse_url($url, $component = -1) {
    return parse_url($url, $component);
}
// Stub other WP-context functions referenced elsewhere in the file but not by our target function.
function unipixel_log() {}
require __DIR__ . '/../public_html/wp-content/plugins/unipixel/functions/unipixel-functions.php';

$cases = [
    // [pattern, url, expected, label]
    ['/thank-you/', '/thank-you/', true,  'exact match'],
    ['/thank-you/', '/thank-you',  true,  'trailing-slash tolerance: pattern has slash, url does not'],
    ['/thank-you',  '/thank-you/', true,  'trailing-slash tolerance: url has slash, pattern does not'],
    ['/thank-you/', '/thank-you/?utm=foo', true, 'path-only match ignores query when pattern has no ?'],
    ['/thank-you/', '/thank-you-page/',    false, 'exact pattern does not match different path'],

    ['/thank-you*', '/thank-you/',          true,  'wildcard suffix matches with trailing slash'],
    ['/thank-you*', '/thank-you/?utm=foo',  true,  'wildcard suffix matches query string'],
    ['/thank-you*', '/thank-you/order/123/', true, 'wildcard suffix matches deep path'],
    ['/thank-you*', '/sample-page/',        false, 'wildcard suffix does not match different path'],

    ['*thank*', '/my/thank-you/page',  true,  'contains: thank present'],
    ['*thank*', '/sample-page/',       false, 'contains: thank absent'],

    ['*', '/anything/at/all',  true,  'any URL matches'],
    ['*', '/',                 true,  'any URL matches root'],
    ['*', '/?x=1',             true,  'any URL matches with query'],

    ['/checkout/?step=2', '/checkout/?step=2', true,  'exact with query string'],
    ['/checkout/?step=2', '/checkout/?step=3', false, 'exact with query string mismatch'],
    ['/checkout/?step=2', '/checkout/',        false, 'exact with query: bare URL does not match'],

    ['/products/*/reviews*', '/products/foo/reviews/',         true,  'middle wildcard'],
    ['/products/*/reviews*', '/products/bar/reviews/?sort=new', true, 'middle wildcard with query'],
    ['/products/*/reviews*', '/products/foo/details/',          false, 'middle wildcard mismatch'],

    ['/Thank-You/', '/thank-you/', true,  'case-insensitive'],
    ['/THANK-YOU*', '/thank-you/abc', true, 'case-insensitive wildcard'],

    // Full-URL inputs (caller might pass full or relative)
    ['/thank-you/', 'http://example.com/thank-you/?x=1', true, 'full URL input, path-only pattern'],

    // Edge cases
    ['',          '/anything', false, 'empty pattern fails closed'],
    ['/',         '/',         true,  'root matches root'],
    ['/',         '/anything', false, 'root does not match deeper path'],
];

$pass = 0; $fail = 0;
foreach ($cases as [$pattern, $url, $expected, $label]) {
    $actual = unipixel_url_pattern_match($pattern, $url);
    $ok = ($actual === $expected);
    if ($ok) {
        $pass++;
    } else {
        $fail++;
        echo "FAIL: [$label]\n  pattern={$pattern} url={$url}\n  expected=" . var_export($expected, true) . " actual=" . var_export($actual, true) . "\n";
    }
}

echo "\nPHP: $pass passed, $fail failed (of " . count($cases) . ")\n";
exit($fail === 0 ? 0 : 1);
