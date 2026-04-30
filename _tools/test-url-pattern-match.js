// Smoke test for unipixelMatchUrlPattern — same case table as the PHP smoke test.
// Run: node _tools/test-url-pattern-match.js

const fs = require('fs');
const path = require('path');

// Stub the browser globals the file reads.
global.window = { location: { origin: 'http://example.com' } };

// Source the file. It assigns to window.unipixelMatchUrlPattern.
const source = fs.readFileSync(
    path.join(__dirname, '..', 'public_html', 'wp-content', 'plugins', 'unipixel', 'js', 'unipixel-common.js'),
    'utf8'
);
// Strip the jQuery-dependent function — we don't need it for this test.
eval(source.replace(/function unipixelLogClientEvent[\s\S]*?^}\s*$/m, ''));

const fn = global.window.unipixelMatchUrlPattern;
if (typeof fn !== 'function') {
    console.error('FAIL: unipixelMatchUrlPattern not defined on window after sourcing');
    process.exit(1);
}

const cases = [
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

    ['/thank-you/', 'http://example.com/thank-you/?x=1', true, 'full URL input, path-only pattern'],

    ['',          '/anything', false, 'empty pattern fails closed'],
    ['/',         '/',         true,  'root matches root'],
    ['/',         '/anything', false, 'root does not match deeper path'],
];

let pass = 0, fail = 0;
for (const [pattern, url, expected, label] of cases) {
    const actual = fn(pattern, url);
    if (actual === expected) {
        pass++;
    } else {
        fail++;
        console.error(`FAIL: [${label}]`);
        console.error(`  pattern=${pattern} url=${url}`);
        console.error(`  expected=${expected} actual=${actual}`);
    }
}

console.log(`\nJS: ${pass} passed, ${fail} failed (of ${cases.length})`);
process.exit(fail === 0 ? 0 : 1);
