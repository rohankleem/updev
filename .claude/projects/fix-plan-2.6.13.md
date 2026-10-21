# Fix plan: validated problems, ranked (2026-10-15)

**Status:** Active. Nothing in this doc has been fixed yet; it is the plan.
**Companion:** `adoption-dropoff-investigation.md` (why we looked), `domain-knowledge/platform-discoveries.md` (RQ-003/004, WP-002, PIN-002, G-003).

**How things were validated.** Live on updev (WP 6.9.4, WooCommerce 10.7, Twenty Twenty-Five block theme and block Cart/Checkout, all five platforms on) with a temporary capture rig: an mu-plugin that intercepted every outbound call to Meta / Pinterest / TikTok / Google / Microsoft / buildio.dev and logged the exact payload (nothing reached the platforms' APIs), plus headless Chrome over DevTools that blocked and logged every browser beacon. A real order was placed through the block checkout (COD #510), plus a qty-3 order (#512), a failed order (#513) and an order whose product was then deleted (#515), fired through `woocommerce_thankyou`. Three parallel read-only code audits filled in the rest. Test tooling is described in `adoption-dropoff-investigation.md`.

Evidence key: **V** = reproduced live, **C** = confirmed in code, **L** = likely (spec or environment dependent, not reproduced).

---

## Done in the working tree (uncommitted, 2026-10-15)

Each verified before/after in a real browser on updev (classic `buildio2` theme, block checkout, all 5 platforms, capture rig), plus server-side checks. Before: problem reproduced; after: fixed; non-Purchase sends identical before/after; no browser exceptions; no PHP warnings; all edited files lint clean (file + stdin).

| Item | Before | After |
|---|---|---|
| #1 Thank-you fatal (deleted product) | fatal at hooks.php:67 | no fatal (dead handler removed) |
| #2 Purchase on reload / failed orders | 2 sends per platform per order+reload, new ID each; failed order sent | 1 send, `event_id = purchase_<order>`; failed order sends nothing |
| #3 OneTrust (Targeting rejected) | `marketing:true`, 6 server sends + 6 beacons | `marketing:false`, nothing sent |
| #7 Malformed requests | HTTP 500 (log endpoint x2, Meta + Google relays) | 400 / handled, no fatal |
| #8 Pinterest event ID | missing on all 5 event types | present on all 5 |
| #9 Item price (3 x $100) | 300 on all 5 platforms | 100 on all 5 |
| #11 Microsoft checkout | `bat.js` exception, begin_checkout lost | no exception, begin_checkout arrives. (`revenue` key claim was a **false positive**: browser purchase already sends `gv=300`.) |
| #12 Relay for a switched-off platform | sent | not sent |
| #15 Requires PHP | header none, readme 7.0 | header + readme 8.0. (`Requires at least` 6.2 **not** changed.) Changelog entry still needed at release. |
| #29 GA4 session_id | not sent | browser's own GA session ID sent |

## Done and committed (2026-10-20)

Tested by running the real seed function and the real platform-settings save handler against a fake `$wpdb` (no database or network touched), before and after.

| Item | Before | After |
|---|---|---|
| #21 Google PageView default | clean install and Google setup wizard: browser + server | browser only (`config/schema.php`, `admin/js/google-setup-wizard.js`). Existing installs unchanged. |

Found while fixing, not fixed: #36 (setup-page save turns browser page view back on).

## Done in the working tree (uncommitted, 2026-10-21)

Tested by running the real functions against fakes (no database or network), before and after.

| Item | Before | After |
|---|---|---|
| #30 Visitor IP behind a proxy/CDN | IPv4 visitor: the proxy's address (`10.0.0.5`) | the visitor's address (`1.1.1.1`); IPv6, no-proxy, private and junk `X-Forwarded-For` cases unchanged |
| #34 GA4 server events | no `ip_override` / `user_agent` | both sent (relay: the collected values; WooCommerce: the visitor's request) |
| #36 Setup-page save on a Google server-only page view | browser 1 / server 1 | browser 0 / server 1 (unchanged); the Google wizard still lands on browser 1 / server 0 |
| #29 Google client ID when Google's tag is blocked (G-004) | page view and site events never sent (browser helper still waiting after 8s); WooCommerce events sent with an empty `client_id` | helper gives up after 3s; the server creates a `_ga` ID (`GA1.1.<random>.<timestamp>`) and Google's tag adopts it (headless Chrome); existing and unreadable `_ga` cookies left alone; product, checkout and order-received pages create it before the page is sent, so View item, Begin checkout and Purchase are covered on classic themes |

## Tier 1: fix first (low risk, high value)

Ordered by harm. Each fix is small and local; most are a few lines.

| # | Problem | Hurts | Ev. | Fix (smallest change) | Risk |
|---|---|---|---|---|---|
| 1 | **Thank-you page fatal** when any order line item has no product (deleted product, or plugins that add product-less line items). Legacy `unipixel_woocommerce_order_completed()` (`functions/hooks.php:35-84`, priority 10 on `woocommerce_thankyou`) calls `$product->get_id()` on `false`. Its output `UniPixelOrderData` is read by nothing, and it prints the buyer's hashed email/phone into page source | Customer sees "critical error" right after paying; Purchase never sent | V (#515: `Call to a member function get_id() on false` at hooks.php:67) | Remove the hook + function (dead code); at minimum `if (!$order) return;` and `if (!$product) continue;` | Very low |
| 2 | **Purchase re-sent on every order-received page load** with a new `event_id` (`'purchase_' . microtime(true)`), to all 5 platforms. Also fires for **failed** orders | Revenue/ROAS inflated; failed payments counted as sales | V (#510 reload: two Purchase sets, different IDs; #513 failed: full Purchase sent) | `event_id = 'purchase_' . $order_id`; return early for `failed`/`cancelled`/`refunded`; `_unipixel_purchase_sent` order meta guard (HPOS-safe `update_meta_data`) | Low (test PayPal-style `pending` returns before blocking `pending`) |
| 3 | **OneTrust: Strictly Necessary group (C0001) mapped to `marketing`** (`js/unipixel-consent.js:45`). Targeting (C0004) never read, so marketing is always true | Visitors who reject advertising are tracked by all platforms, browser + CAPI | V (OneTrust cookie with `C0004:0` -> summary `marketing:true` -> Meta Lead/PageView, Pinterest, TikTok, Microsoft all sent) | `C0004` -> marketing, `C0002` -> performance, `C0003` -> functional; mirror in PHP | Very low (correctly lower volumes for OneTrust stores) |
| 4 | **Block themes only: browser-side ViewContent and Purchase never fire.** On block themes `woocommerce_before_single_product` / `woocommerce_thankyou` run before `wp_enqueue_scripts`, so `wp_add_inline_script('unipixel-common', ...)` / localize silently no-op. **Classic themes are fine** (re-verified: browser ViewContent fires for Meta, Pinterest, GA, and its `eid` matches the server event). Server-side fires on both. Scope: block-theme stores (WP default themes Twenty Twenty-Four/Five and other FSE themes); most WooCommerce stores still run classic themes (Astra, Kadence, Storefront, Flatsome, Divi, Hello) | Block-theme stores: no browser+server pair to dedupe; Pixel Helper shows no ViewContent/Purchase | V (block: hook ran with `unipixel_common_registered: false`, no ViewContent globals/`fbq` call in HTML, no beacons; classic: all present and fired). Purchase on classic not separately re-run (same code path) | Queue the script/localize data and attach on `wp_enqueue_scripts` (late priority) when called before that action has run. While there: Meta browser ViewContent reads a non-existent key (`UniPixelViewContentMeta.price`, `client-side-send-viewcontent.php:22`) so `cd[value]` is missing (seen on the classic beacon); use `.value` | Low |
| 5 | **Cookiebot consent never parses** (real cookie is a JS object literal, not JSON): `JSON.parse` / `json_decode` fail, so marketing stays false even after Accept All. Moove is split on `,`/`:` and never finds its key (C) | Cookiebot/Moove stores with consent honouring send nothing at all | V (Cookiebot Accept-All cookie -> `marketing=false`) | Regex-parse Cookiebot (`marketing:true`); `json_decode` Moove; normalise each vendor to the four canonical keys | Low |
| 6 | **Consent "Third Party" trap.** First visit to Consent Settings saves `consent_ui = 0` (Third Party). Turning Honour on then blocks everything (no banner, no server sends) when no supported CMP exists | "Turned on consent, nothing works" | V | Default `consent_ui` to `'unipixel'` (`page-consent-settings.php:16`, `unipixel-enqueue.php:41`); warn when Third Party selected and no supported CMP plugin is active | Low |
| 7 | **Anonymous fatal in the 2.6.12 hardening.** `strlen($_POST['json_data_sent'])` with `json_data_sent[]=a` throws TypeError (`ajax-handle-log-client-event.php:15`); same pattern for `pageUrl[]` in relays | Anyone can trigger HTTP 500 + WordPress "technical issue" emails naming UniPixel | V (HTTP 500, `Uncaught TypeError: strlen()` in debug log) | `is_string()` checks before `strlen()` / `esc_url_raw()`; `?? ''` on all relay `$_POST` reads | Very low |
| 8 | **Pinterest `event_id` passed in the callback slot** on every client call incl. Purchase; beacons carry `ed={}` | Pinterest double-counts browser + server conversions | V | Move `event_id` into the payload object (6 call sites) | Low |
| 9 | **Line-item price = line total** (qty 3 x $100 sent as `price: 300`) on all 5 platforms | GA4 item revenue 3x; catalogue values wrong | V (#512) | `get_total() / max(1, get_quantity())` at `get-common-woo-data-purchase.php:54` | Low |
| 10 | **US phone hashed without country code** (`sha256("4155552671")`); TikTok/Microsoft need `+` E.164 | Lost phone match for US shoppers | V (#512) | Only treat as international when raw input starts with `+`/`00`; prepend calling code from billing country; hash `+digits` for TikTok/Microsoft | Low (hashes change, intended) |
| 11 | **Microsoft `ecomm_pagetype: 'checkout'`** is not an allowed UET value (`bat.js` throws on checkout); browser purchase uses `revenue` instead of `revenue_value` | Microsoft begin_checkout lost; purchase revenue not recorded | V / C | `'checkout'` -> `'cart'` (or `'other'`) at `client-side-send-checkout.php:237`; `revenue` -> `revenue_value` at `client-side-send-purchase.php:234` | Very low |
| 12 | **Relays ignore `platform_enabled`** (only check `serverside_global_enabled`) | Turning a platform off doesn't stop server events from cached pages or forged calls | V (TikTok off -> relay still sent) | Return early when `platform_enabled` is 0 in all 5 relays | Very low |
| 13 | **Fresh installs default to blocking API calls.** Seeds turn response logging on for AddToCart/InitiateCheckout/Purchase on every platform and Meta ViewContent (`schema.php:258` defaults to 1 when the key is missing). Logged sends are `blocking => true, timeout => 2.5` during page render | Every product page waits on Meta; checkout/thank-you wait on 4-5 APIs | V (fresh DB seeds) | Seed `send_server_log_response = 0` everywhere (users switch it on to debug); fix the help text at `unipixel-functions.php:497` | Low |
| 14 | **(Not a deactivation driver: telemetry shows 1 of 266 deactivating sites saved settings without enabling a platform.)** **Manual setup saves "successfully" while tracking is off** (platform and server-side switches default off; only the wizards turn them on). Status strip / home badge go green regardless of switches | Core "set it up, can't see it working" path | C | Warn on save when Pixel ID present but platform off, or token present but server-side off; add the switch to the "Start simple" copy; amber "verified but switched off" state | Low (copy + notice) |
| 15 | **Requires PHP 7.0 / Requires at least 5.0 are wrong** (needs PHP 8.0 for `match`, WP 6.2 for `%i`) | PHP 7.x sites (~20% of WP) get an activation fatal instead of WP's clear block | V | `Requires PHP: 8.0`, `Requires at least: 6.2` in readme + plugin header | None |
| 16 | **Half the admin-ajax calls do nothing.** `unipixel_log_client_event` fires for every client event and is discarded when storage is off (default for PageView): ~5 of ~9 calls per page view | Host load; each call boots WordPress, uncacheable | V (`log_id: null` on every call) | Localize the dbstore flags; skip `unipixelLogClientEvent()` when that event type isn't stored | Low |
| 17 | **Product-page HTML carries the first visitor's IP, user agent, fbp/fbc, gclid and a fixed event_id** via the console-logging localize, printed unconditionally (`hook-handlers-viewcontent.php:104`, `localize-and-send-consolelogging.php:11-16`). Cached pages serve it to everyone (classic themes) | Personal data in cached HTML; shared event_id/click IDs | C | Print console payloads only when the console logger is on and the viewer is an admin | Low |
| 18 | **Meta CAPI posts to Graph API v14.0** (expired; Test Connection uses v18.0, also expired). Verified 2026-10-15: v14.0 still answers exactly like v23.0 (no deprecation error), so it works today; not urgent | Silent breakage if Meta ever drops the fallback | V | One constant for the Graph version, set to current, used by send + Test Connection | Low |
| 19 | **AddToCart redirect / Store API path still wraps scripts in `jQuery(document).ready`** (`client-side-send-addtocart.php:293,413,511,630,714`) | Pages without jQuery throw and drop all platforms' AddToCart scripts in that block | C | `unipixelOnReady(function(){ ... })` | None |
| 20 | **~40 extra DB queries per page** (platform row re-read per hook, events per platform, transient reads even without WooCommerce) | Slower TTFB everywhere | V (39 of 125-154 queries) | Static per-request cache in `unipixel_get_platform_settings()`, `unipixel_get_events_for_platform()`, `unipixel_woo_event_get_settings()`; read ATC transients only when a cookie flag says one is pending | Low |
| 21 | **Google page_view sent browser AND server by default** (seed + wizard); GA4 doesn't dedupe non-purchase events (G-001) | Every GA4 page view counted twice | V (fresh seed) | Seed Google `pageview_send_serverside = 0`; wizard sends 0 for Google. **Done and committed (2026-10-20).** | Low |
| 22 | **Fresh-install PHP warnings** on General Settings (`enableGoogleDebugView*` keys missing from seeded logging options) | Visible warnings with display_errors on | V | Add the keys to the seed; `array_merge` defaults on read | None |
| 23 | **`debug_updev.log` shipped in wp.org tag 2.6.11** (harmless contents: other plugins' notices + a local path); still sits in the plugin folder and isn't excluded by `_obf/exclude-list.txt` | Hygiene / reviewer optics | V (`tags/2.6.11/debug_updev.log`) | Delete the file; add `*.log` to the exclude list; add a "no .log files" post-export check | None |
| 34 | **GA4 server events drop the visitor's IP and user agent.** The relay collects them (`google-ajax-listener-send-server.php:82-83`) but `unipixel_send_server_event_google()` (`send-server-event.php:100`) sends only `client_id`; the WooCommerce prepare functions don't collect them for Google at all. The Measurement Protocol has accepted `ip_override` (2025-05-14) and `user_agent` / `device` (2025-06-11) | Server events reach GA4 without the visitor's own location or device: GA4 borrows them from tag data for that `client_id`, and with none, location is blank and device defaults to desktop | C + Google MP changelog | Send top-level `ip_override` and `user_agent` from the visitor's request in `unipixel_send_server_event_google()` (covers relay and WooCommerce paths); `device` / `user_location` not needed. Placement confirmed against Google's MP reference (top-level strings). IP accuracy depends on #30. **Done in working tree, uncommitted (2026-10-21).** | Low |
| 36 | **Setup-page save turns browser page view back on**, every platform: `handler-platform-settings.php` defaults `pageview_send_clientside` to 1 when not posted (line 43) and always writes it (line 78); the setup form never posts it (`ajax-platform-settings.js:52`) | Google site set to server-only page view goes back to both (double count, G-001); other platforms' Events-page choice silently overridden | C | Only write it when posted, as 2.6.9 did for `pageview_send_serverside`. **Done in working tree, uncommitted (2026-10-21);** the Google wizard now posts browser 1 explicitly so it still lands on browser only | Low |

## Tier 2: high value, medium risk (careful change + real-platform check)

| # | Problem | Ev. | Fix direction | Risk |
|---|---|---|---|---|
| 24 | **Server sends block page render.** "Non-blocking" `wp_remote_post` waits the full round trip (measured 0.34s Meta, 0.71s Microsoft; WP floors timeouts to 1s). Product page sends 4 ViewContents during render, checkout 4 InitiateCheckouts, thank-you 5 Purchases | V | Queue sends during render; flush on `shutdown` after `fastcgi_finish_request()` / `litespeed_finish_request()`; fall back to Action Scheduler (`as_enqueue_async_action`) | Medium |
| 25 | **THEORY, unconfirmed: stale nonces on cached pages** (WP-002). Only verified: a nonce >24h old gets `403 -1`. No evidence any real site serves pages that old; affects browser-relayed events only (PageView, site events), not WooCommerce AddToCart/InitiateCheckout/Purchase. Confirm on a real cached site before doing anything | Theory | Drop the page-embedded nonce gate on public tracking endpoints; replace with same-site `pageUrl` host check + per-IP rate limit (also fixes #26) | Medium |
| 26 | **Anyone can forge configured server events** (e.g. `Lead`) with the public nonce, arbitrary `event_source_url`, caller-controlled click-ID cookies, unlimited rate. Purchase is not forgeable | V | `pageUrl` host must equal site host; verify url-trigger events with `unipixel_url_pattern_match()`; per-IP/event transient rate limit; cap `event_id` | Low-Medium |
| 27 | **Consent leaks before and after Reject** (G-003 extended): UET `pageLoad`/`pageHide`, GA4 enhanced measurement + `_ga` cookies, TikTok auto `act`/`inter` events, Meta `_fbc` cookie all sent with consent declined. `adStorageConsent` hard-coded `'G'` | Claude's claim; Rohan disputes the principles (2026-10-21) | When honouring is on: GA Consent Mode default denied + update on grant; UET consent default denied; TikTok `holdConsent`/`grantConsent`; Meta `fbq('consent','revoke')` before init. Or defer base libraries until grant | Medium |
| 28 | **TikTok server events nest `user` and `page` under `context`** (legacy shape) on `/v1.3/event/track/` | V (shape) / L (impact) | Move `user`/`page` to top level of `data[0]`, `transaction_id` -> `order_id`; same in Test Connection; verify in TikTok Test Events | Low code, needs TikTok check |
| 29 | **GA4 MP: no `session_id`; `client_id: ''` when no `_ga` cookie** (GA drops it) | V | **session_id: done in working tree, uncommitted (2026-10-15).** `unipixel_get_ga4_session_id()` in `unipixel-functions.php` + 6 lines in `unipixel_send_server_event_google()`. Verified: real-browser site event and real block-checkout purchase (#516) both sent the browser's own GA session ID; 9 parser edge cases pass. Still to confirm on GA's side (DebugView/Realtime). **Empty client_id: fixed differently, uncommitted (2026-10-21):** the server now creates the ID when it's missing, and the browser helper no longer hangs when Google's tag is blocked (G-004). Product, checkout and order-received pages create the ID before the page is sent. | Low |
| 30 | **Client IP behind proxies**: IPv4 `X-Forwarded-For` ignored, any IPv6 XFF trusted | V (sent `10.0.0.5` not `203.0.113.5`) | Trust `CF-Connecting-IP` / XFF only from known proxies; `unipixel_client_ip` filter; ad matching only. **Simple version done in working tree, uncommitted (2026-10-21):** first `X-Forwarded-For` address used when public (IPv4 or IPv6), else `REMOTE_ADDR`. Known-proxies version not done | Low-Medium |
| 31 | **Microsoft page views counted twice** (UET auto `pageLoad` + custom `PageView` + CAPI `pageLoad` with an unshared `eventId`) | V | Drop the custom PageView for Microsoft; no server pageLoad when the UET tag is included | Low |
| 32 | **Log endpoint stores unbounded junk**: only `json_data_sent` capped; `response_log_message` / names uncapped; any event name stored; prune by row count only | C | Ignore client `response_log_message`; truncate to 255; only store configured event names; cap `sent_data` | Low |
| 33 | **Access tokens copied into transients** on the AddToCart redirect path (`hook-handlers-addtocart.php:131` etc., unredacted `args`) | C | Store only `payload`/`response` in the transient | Low |
## Tier 3: product / UX / robustness (bigger, or design calls)

- **No first-run path** (no redirect, no Settings link on Plugins row, no checklist; home lacks a Microsoft card). C.
- **Stored Event Logs empty on non-WooCommerce sites** (PageView storage off by default); **consent-blocked rows mislabelled**. C.
- **Event log says "Successful" on any HTTP 200** even when TikTok/Microsoft/GA4 report errors in the body. C.
- **ViewContent on cached product pages** replays one `event_id` to everyone; move to client-first. L.
- **AJAX error messages** generic ("error Forbidden"); no "session expired, reload" hint. C.
- **Event Test Console** only works same-origin, same browser, tab open; cleared every page load. C.
- **Schema upgrade**: no lock, migration flags set even when ALTER fails, no `get_charset_collate()`, default rows can duplicate under concurrency. C/L.
- **Uninstall leaves everything** (6 tables incl. plaintext tokens and IP-bearing logs). C. Offer opt-in "delete data on uninstall".
- **readme.txt overpromises** ("paste credentials and server-side is live", CookieAdmin support, Pinterest/Google dedup claims). C.
- **GA4 MP always uses the global endpoint**; Google added `region1.google-analytics.com` (2025-05-14) for EU collection. Option for EU sites. The `event_id` param UniPixel adds to MP events is not a GA4 dedup key (custom parameter only); leave it, it doesn't break tracking (Rohan, 2026-10-21). C.
- **Google tracking strategy** (needs a plan; facts and sources in G-001): Google tag gateway through a PHP relay, as Site Kit does (no CDN); Google Ads conversions through the Data Manager API (tag + API dedup on `transactionId`, open to all accounts since 2026-02-10); GA4 multi-source dedup once Google's allowlist opens; server as fallback for blocked visitors rather than a duplicate.

## Policy decisions (yours, not code risk)

- **Hex-encoded obfuscation** (RQ-003): out of scope for this plan. Rohan's call (2026-10-15): scanner quarantine is the host/user's issue; obfuscation is a separate decision for another time.
- **Telemetry without opt-in** (`unipixel_metric_log` sends `site_url` to buildio.dev on activation and every settings change): wp.org Guideline 7 expects opt-in. Also lossy (adoption doc).
- **Tested up to 6.9** while WP 7.1 is current: test on 7.1 (proposed as its own session), then bump.
- **Existing installs on the old #21 default: no migration** (Rohan, 2026-10-21). Their Google Events page shows both page view boxes ticked and owners control the toggles; #21 was about the clean-install default.

## Checked and fine

- Activation on a fresh site (shipped build and source, with/without WooCommerce): clean, no notices, all admin pages render.
- Meta and TikTok browser/server `event_id` match for PageView, AddToCart, InitiateCheckout.
- InitiateCheckout fires on the block Checkout (the commented-out hook note in `testing.md` is stale).
- URL-trigger custom event (`Lead` on `/contact/`) fires browser + server.
- Server-side consent gating with the UniPixel banner: nothing sent to any platform API after Reject.
- Pinterest Tag event names `initiatecheckout` / `viewcontent` are valid (Pinterest added them).
- Purchase cannot be forged through the public endpoint; `custom_data` is always empty on relays.
- All SQL is prepared; the admin log page escapes stored fields (no stored XSS from public log rows); 2.6.12 redaction covers the log table and AJAX responses for all 5 platforms.
- 2.6.11 `unipixelAjaxPost` encodes like `jQuery.param`; no jQuery in the main watchers.
- No UniPixel PHP notices/warnings in the debug log across the normal live flows.
- 2.6.12 admin-script hook guard (`unipixel_page_` prefix) is safe: the menu title is a literal `'UniPixel'`.
- Suspected consent timing gap (summary rebuilt on `load`) did not reproduce: events fired on the first page after Accept.

## Suggested 2.6.13 bundle

Tier 1 #1-#23 are independent, small, and verifiable with the capture rig. A sensible first cut is #1, #2, #3, #4, #5, #6, #7, #8, #9, #11, #12, #13, #15, #16, #23 (the correctness, consent and crash fixes), with #10, #14, #17-#22 riding along if time allows. Tier 2 #24-#27 are the next release's theme ("server-side that works on real hosts").

## Re-test after fixes (use the capture rig)

1. Block checkout order, reload order-received twice: one Purchase per platform, `event_id = purchase_<order_id>`, browser beacons present on Twenty Twenty-Five.
2. `woocommerce_thankyou` for a failed order: nothing sent. For an order with a deleted product: no fatal.
3. Qty-3 order: item `price` = unit price everywhere. US phone `(415) 555-2671`: Meta `ph` = sha256(`14155552671`).
4. OneTrust cookie with `C0004:0`: nothing sent. Cookiebot Accept-All cookie: events sent.
5. Checkout on Microsoft: no `bat.js` exception. Pinterest beacons carry `event_id` in `ed`.
6. `json_data_sent[]=a` / `pageUrl[]=a`: 4xx, no fatal. Relay to a disabled platform: skipped.
7. Fresh install: no blocking (`timeout 2.5`) calls on product page; UniPixel queries per page and admin-ajax calls per page view both roughly halved.
