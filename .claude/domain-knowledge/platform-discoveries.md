# Platform Discoveries — Event Quality Investigation

Cross-session audit findings as platform reports are assessed and code is audited. When a platform diagnostic is investigated, the outcome lands here (fixed, dismissed as platform overreach, noted for later, etc.).

---

## TikTok

### TT-001: Event ID mismatch (57%) — Mostly Environmental

**Source:** TikTok Umbrella Diagnostics — "Event ID mismatch between server and browser events"
**Severity reported:** Medium | **Events affected:** 57.02%

**Code audit result:** The event_id flow is correct in both patterns:
- **Server-first (WooCommerce):** event_id generated once in PHP (`purchase_<microtime>`), passed to both TikTok API and client pixel via `wp_localize_script`. Same value reaches `ttq.track()`.
- **Client-first (custom/PageView):** event_id generated once in JS (`event_<timestamp>`), passed to both `ttq.track()` and AJAX → server API. Same value used.

**Root cause assessment:** Not a code logic bug. The 57% is likely **environmental** — one side fires but the other doesn't:
- Ad blockers prevent TikTok SDK (`events.js`) from loading → `ttq.track()` never executes → server has event_id, browser doesn't
- User leaves thank-you page before inline JS runs
- AJAX failures on client-first events → browser fires, server never receives
- TikTok counts unpaired event_ids as "mismatches"

**Action:** No event_id code change needed. Monitor after other fixes land to see if percentage shifts.

**Related bug found:** See TT-002.

---

### TT-003: Reserved event names auto-map to Standard events — TERMINOLOGY QUIRK

**Source:** TikTok Events API docs (Custom Events / Reserved Events)
**Surface impact:** Bespoke event flow (centralised Event Manager + per-platform Custom Events table)

**The quirk:** TikTok defines a set of "Reserved Event Names" that the platform automatically maps to its existing Standard Events. If a user creates a Bespoke event with a Reserved name, TikTok will NOT report it under that bespoke name — it gets silently rolled into the matched Standard event. The user sees no custom-named data flowing.

**Cross-reference:** `event-terminology.md` § Platform-specific quirks. The per-platform inline hint for TikTok carries "Avoid Reserved names" as the warning surface.

**Action when surfacing in UI:** When a user enters a bespoke Platform Event Reference for TikTok, validate against the reserved list and warn inline. Don't block (TikTok still accepts the call); just inform.

---

### TT-002: Parameter ordering bug in Purchase & Checkout server calls — FIXED

**Found during:** TT-001 investigation
**Files affected:**
- `woocomm-hook-handling/hook-handlers-purchase.php` (line ~163)
- `woocomm-hook-handling/hook-handlers-checkout.php` (line ~217)

**The bug:** Function `unipixel_send_server_event_tiktok()` expects:
```
arg 6: $pageUrl (string)
arg 7: $sendServerLogResponse (bool)
arg 8: $consentAlreadyChecked (bool)
```
Purchase and Checkout were passing:
```
arg 6: !empty($wooEventSettingsTikTok->send_server_log_response)  ← boolean, should be URL
arg 7: $consentAlreadyChecked                                      ← shifted
arg 8: (missing)                                                   ← defaults to false
```

**Impact:** `$pageUrl` received `true/false` → TikTok API got a boolean instead of a URL in `context.page.url`. Consent check was also bypassed (always defaulted to false).

**Not affected:** AddToCart and ViewContent — they pass `$dataToSendTikTok['pageUrl']` correctly.

**Fix:** Added `$pageUrl` argument in correct position. Session: 2026-03-01.

---

## Meta

### META-001: "Send valid currency information" for Lead & ConfiguratorShownPrice — DISMISSED

**Source:** Meta Pixel diagnostics (3 days old, may already be stale)
**Events affected:** 100% of Lead, 100% of ConfiguratorShownPrice

**What Meta claims:** These events aren't sending valid currency info. Suggests 5% ROAS improvement.

**Assessment:** These are client-first custom events — user picks an element + trigger + event name. No monetary value is involved. The server-side send exists purely for deduplication counting. Neither client (`event_params = {}`) nor server (`$sanitized_custom_data = []`) sends any custom_data at all — no rogue blank currency field, just correctly empty payloads.

Meta applies its e-commerce data checklist to all standard event names (like "Lead") regardless of context. "ConfiguratorShownPrice" is a custom event name that happens to contain "Price" — Meta flags it by pattern.

**Verdict:** Platform overreach, not a code problem. No action needed. The 5% ROAS claim is Meta's canned upsell messaging.

---

### META-002: Purchase EMQ 7.4/10 — fbc "not sent" + external_id + Facebook Login ID — DISMISSED (fbc), NOTED (external_id)

**Source:** Meta Events Manager — Purchase event shared parameters report

**What's already at 100%:** Email (hashed), IP, User Agent, Phone (hashed), fbp — all green.

**fbc (Click ID) — DISMISSED.** Meta says "server is not sending fbc." Code audit confirms fbc IS sent when available:
- All 4 WooCommerce prepare functions call `unipixel_get_fbc_value()` and include fbc when `strlen > 5`
- Dual capture: JS (`pixel-meta.js`) and PHP (`hooks.php`) both capture fbclid → `unipixel_fbclid` cookie (90 days)
- Fallback: if `_fbc` cookie missing but `unipixel_fbclid` exists, constructs valid `fb.<domainIndex>.<timestamp>.<fbclid>` string
- Client-first AJAX handler (`meta-ajax-listener-send-server.php`) also includes fbc
- Low coverage is because most users don't arrive via Facebook ad clicks → no fbclid → legitimately empty. Plugin already does what Meta's "parameter builder" does.

**External ID — ASSESSED & DEPRIORITISED.** Meta claims 15.52% match improvement from `external_id`. After full analysis (2026-03-14), this is a platform upsell metric, not a genuine quality gap for WooCommerce stores.

**What external_id does:** Gives Meta YOUR persistent identifier for a user, so they can link that user's activity across advertisers in their network. It's Meta building their identity graph using your data.

**Why it doesn't help typical WooCommerce stores:**
- Most stores are guest checkout — there IS no persistent user identifier until purchase (too late for the funnel).
- The strongest cross-device identifier (email) is already sent as `em` via Advanced Matching.
- Platform cookies (`_fbp`, `_ttp`, `_ga`, `_epik`) already handle within-session event linking — a WooCommerce session key would be redundant.
- Social login IDs would be genuinely useful but depend on third-party plugins, making it unreliable as a core feature.
- Hashed WP user ID only covers logged-in users (~10% of typical store traffic).
- Meta's 15.52% claim is an average across ALL advertisers, skewed by high-login-rate sites (SaaS, subscriptions, social platforms). For guest-checkout e-commerce, the real impact is near zero.

**Available identifiers assessed:**

| Identifier | When available | Cross-device? | Already sent elsewhere? |
|---|---|---|---|
| WP user ID (hashed) | Logged-in only | Yes (if they log in) | No |
| WC session key | Always | No | No — but `_fbp` covers this |
| Billing email (hashed) | Purchase only | Yes | Yes — `em` in user_data |
| Platform cookies | When pixel loads | No | Yes — `fbp`, `ttp`, etc. |

**Verdict:** Not implementing. Every useful identifier is either already sent as a dedicated field or has the same limitations as platform cookies. The quality wins that matter are the ones hitting 100% of traffic (dedup accuracy, AJAX delivery, firing precision). Reference this analysis when customers ask about platform match quality scores.

**Facebook Login ID — DISMISSED.** Meta pushing their login ecosystem. Not realistic for a WordPress plugin.

**IPv6 — LOW PRIORITY.** Depends on `unipixel_get_ip_address()` implementation. Minor.

---

### META-003: Events Manager CAPI tokens are dataset-scoped — can send events, nothing else (found 2026-07-12, fixed in 2.6.11)

**Source:** Live false alarm on steelchief.com.au — Test Connection rejected a freshly generated, fully working token.

**The token shape:** The token Events Manager generates today ("Generate access token" under Settings, Conversions API) is a SYSTEM_USER token from Meta's internal "Conversions API Application". `debug_token` reports `scopes: ["read_ads_dataset_quality"]` and a `granular_scopes` grant limited to that one dataset. It carries **no `ads_management`**, and it **cannot read the Pixel node** (`GET /{pixel_id}` returns `(#100) Missing Permission`). The only thing it can do is POST to `/{dataset_id}/events` — which it does perfectly (verified live: `events_received: 1`).

**What this broke:** Test Connection (2.6.7–2.6.10) gated on the `ads_management` scope and then on a Pixel-node read. Both checks fail for this token shape, so the most common token Meta hands users was reported as broken, with advice ("regenerate in Events Manager") that produces another token of the same shape. Old-style Business Settings system-user tokens (full `ads_management` scope) passed — which is why our own dev-box tokens never tripped it.

**The rule:** Never gate Meta credential validation on classic scope strings or Graph node reads. The only authoritative capability check is delivering a `test_event_code`-tagged event to `/{pixel_id}/events` and confirming `events_received`. Bonus diagnostic: `granular_scopes[].target_ids` names the dataset the token belongs to — if it doesn't contain the entered Pixel ID, the user pasted a token from a different Pixel (2.6.11 surfaces exactly that message).

**Related:** test events with `test_event_code` appear only in Events Manager's Test events tab and never land in real data — safe to send from a diagnostic.

## Cross-Platform — AddToCart Event

### ATC-001: AJAX add-to-cart client pixel never fires — FIXED

**Found during:** AddToCart quality assessment (2026-03-14)
**Files affected:** `woocomm-hook-handling/hook-handlers-addtocart.php`, `woocomm-hook-handling/client-side-send-addtocart.php`, `woocomm-hook-handling/client-side-localize-addtocart.php`

**The problem:** When WooCommerce uses AJAX add-to-cart (default on shop/archive pages in most themes), the `woocommerce_add_to_cart` hook fires inside an AJAX request context. The server event fires correctly. But the client pixel is injected via `wp_add_inline_script('unipixel-common', $script)` — which only outputs when WordPress renders a full HTML page. WooCommerce AJAX add-to-cart returns JSON via `wp_send_json()`, so the inline script never reaches the browser.

**How it was detected in code:** In `hook-handlers-addtocart.php`, `$isAjax = wp_doing_ajax()`. When true, `unipixel_inline_script_*_addtocart('')` was called with empty trigger hook — hitting the `else` branch that does `wp_add_inline_script()` directly during the AJAX context. This did nothing because AJAX returns JSON.

**Impact:** Platforms received server-only AddToCart events for AJAX flows. No corresponding browser pixel hit. Reduced match quality and deduplication was meaningless (only one side fired).

**Fix (2026-03-14):** Implemented `woocommerce_add_to_cart_fragments` filter to deliver pixel scripts via the AJAX JSON response. WooCommerce's JS applies fragments to the DOM and triggers `added_to_cart`. All 5 platforms (Meta, Google, TikTok, Pinterest, Microsoft) now fire client pixels after AJAX add-to-cart.

**How it works:**
- `UniPixel_AddToCart_Fragment_Collector` static class accumulates platform data and server results within the single AJAX request lifecycle
- `hook-handlers-addtocart.php` AJAX branches push client data to the collector instead of calling `wp_add_inline_script()`
- `unipixel_addtocart_fragments()` filter reads the collector and builds a combined `<script>` inside a hidden div (`div.unipixel-addtocart-fragment`)
- Each platform's pixel call is wrapped in an IIFE with inline JSON data (no `wp_localize_script` dependency)
- Console logging for server results is also included in the fragment
- A placeholder `<div class="unipixel-addtocart-fragment">` is output via `wp_footer` so WooCommerce has a target to replace

**Coverage after fix:**

| Scenario | Server event | Client pixel | Mechanism |
|---|---|---|---|
| WooCommerce AJAX add-to-cart (most themes, shop/archive pages) | Hook fires during AJAX | **Fragments** | Fragment injected into AJAX JSON response, WooCommerce JS applies to DOM |
| POST redirect add-to-cart (single product page default) | Hook fires during POST | Transient → next page `wp_footer` | Existing transient relay, unchanged |
| Custom theme AJAX (non-WooCommerce endpoint) | Hook fires during AJAX | Transient fallback on next page | Falls through to existing path |
| WooCommerce Blocks (Store API) | May not fire `woocommerce_add_to_cart` hook | Neither | Known limitation, future work |

---

### ATC-002: Transient user identifier collision risk for guests — PARTIALLY FIXED

**Found during:** AddToCart quality assessment (2026-03-14)
**Files affected:** `woocomm-hook-handling/client-side-send-addtocart.php` (all platform functions)

**The problem:** Guest users are identified by `md5(IP + User-Agent)`. Multiple guests behind the same NAT/VPN with the same browser share a transient key. One user's add-to-cart data could overwrite another's, or fire the wrong pixel on the wrong session.

**Duplicated identifier logic — FIXED (2026-03-14).** All 10 inline `is_user_logged_in() ? get_current_user_id() : md5(IP+UA)` blocks consolidated to use centralized `unipixel_get_user_identifier_for_transient()` from `unipixel-functions.php`.

**Collision risk — OPEN.** The underlying `md5(IP + User-Agent)` approach still has a theoretical collision risk for guests behind the same NAT. A future improvement would be to use `WC()->session->set()` / `WC()->session->get()` instead — WooCommerce sessions are keyed to the actual user session cookie, not IP+UA. Low priority — the AJAX fragments path (ATC-001 fix) bypasses transients entirely for the most common add-to-cart flow, reducing the surface area where this matters.

---

## Release Quality — Recurring Issues

### RQ-001: Smart quotes (Unicode curly quotes) keep entering PHP files

**Occurrences:**
- **v2.5.3:** 15 PHP files shipped with U+2018/U+2019 (single curly quotes). Caused fatal errors on all WooCommerce events. Fixed in v2.5.4.
- **v2.6.0 pre-export:** 4 PHP files had smart quotes again (class-unipixel-log.php, unipixel-functions.php, tiktok-enqueue.php, prepare-common-to-platform-purchase.php). All in comments — no runtime impact, but still a risk.

**How they get in:** Code pasted from tools that auto-convert straight quotes to curly: ChatGPT web UI, Google Docs, Word, Notion, some clipboard managers. The characters are U+2018 `'`, U+2019 `'`, U+201C `"`, U+201D `"`. In PHP string context they cause parse errors. In comments they're inert but signal contamination.

**Pre-export check (mandatory):**
```bash
grep -rl $'\xe2\x80\x98\|\xe2\x80\x99\|\xe2\x80\x9c\|\xe2\x80\x9d' --include="*.php" .
```
Must return empty. If not, fix with:
```bash
sed -i "s/\xe2\x80\x98/'/g; s/\xe2\x80\x99/'/g; s/\xe2\x80\x9c/\"/g; s/\xe2\x80\x9d/\"/g" <files>
```

---

### RQ-002: Stray closing quotes in multiline PHP strings

**Occurrence:** v2.6.0 — `unipixel-functions.php` line 158 had a premature `"` closing the string mid-sentence in the `Google_Gtm` help text. The next line (`<br/><b>3.</b>`) became bare PHP code.

**Why it wasn't caught locally:** `php -l filename` on the unobfuscated multiline source file passed. The WordPress.org SVN pre-commit hook lints via stdin (`php -l` on piped/collapsed content), which is stricter after obfuscation collapses the whitespace.

**How it got in:** Likely a manual edit that split a string across lines and left a stray `"` at the end of one line. It was latent — present in the source for an unknown period.

**Pre-export check (mandatory):** After running `obf.sh export`, lint ALL obfuscated PHP files:
```bash
EXPORT="C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports"
for f in "$EXPORT"/**/*.php "$EXPORT"/*.php; do
  result=$(php -l "$f" 2>&1)
  echo "$result" | grep -qi "error" && ! echo "$result" | grep -q "No syntax errors" && echo "FAIL: $f" && echo "$result"
done
```
Must produce no FAIL output. Also lint via stdin to match the SVN hook behaviour:
```bash
cat "$EXPORT/functions/unipixel-functions.php" | php -l 2>&1
```

> The pre-export checklist itself lives in `app-knowledge/deploy-and-release.md` — this section captures *why* each check is in the list.

---

### RQ-003: Host malware scanners quarantine obfuscated files, plugin then fatals (FIELD INCIDENT, 2026-10-14, OPEN)

**Occurrence:** wp.org thread "Unable to activate the plugin" (aftab1003, sweetonic.pk, fresh install of 2.6.12). cPGuard quarantined `trackers/meta-enqueue.php` (7136 bytes, identical in every tag 2.5.3 to 2.6.12) as `Malware_Content`. `unipixel.php` loads every tracker with a bare `require_once`, so the missing file fatals and WordPress refuses activation ("Plugin could not be activated because it triggered a fatal error").

**Likely trigger:** the `--encode-strings` obfuscation pass. Every `trackers/*-enqueue.php` ships a ~245-char hex-encoded JavaScript string fed to `wp_add_inline_script` (the "Platform tracking disabled" console line). Hex-encoded JS injected into front-end pages is the textbook signature of injected malware. The clean source has nothing a scanner would object to beyond the standard Meta noscript pixel. Not confirmed against cPGuard's rules (closed product); only meta was reported, but the other four enqueue files carry the same pattern.

**Wider exposure:** if a scanner quarantines a file on a site where UniPixel is already active (signature update, nightly scan, auto-update), the same `require_once` fatals every page load, not just activation.

**Policy angle:** wp.org Detailed Plugin Guideline 4 ("Code must be (mostly) human readable") says obscured code is not permitted in the directory and calls it "a common vector for hidden, malicious code". It also requires public access to source and build tools when shipping non-readable code. Hex-encoded strings are squarely in scope; a public "malware?" thread is the kind of thing that triggers a plugin review.

**Priority (Rohan, 2026-10-15):** not a priority. Scanner quarantine is treated as the host/user's issue, not a plugin defect; any change to obfuscation is a separate decision for another time. Don't rank it alongside functional fixes.

**Fix options (if ever decided):** drop `--encode-strings` from `FLAGS` in `_obf/obf.sh` (removes the trigger; the post-export "every PHP carries `\xNN`" check in `deploy-and-release.md` must change with it); optionally guard tracker includes so a missing file degrades with an admin notice instead of fataling.

---

### RQ-004: PHP 8 syntax shipped under `Requires PHP: 7.0`

`admin/admin-wpmenu.php` uses a `match` expression (PHP 8.0+). It is loaded unconditionally via `admin/admin.php`, and has shipped since at least 2.1.0. The readme declares `Requires PHP: 7.0` and the plugin header declares nothing, so WordPress lets PHP 7.x sites activate and they get the same generic activation fatal as RQ-003. Per `api.wordpress.org/stats/php/1.0/` (2026-10), PHP 7.0-7.4 is ~20% of WordPress sites, and those failures never reach telemetry (the activation hook never runs). Fix: declare `Requires PHP: 8.0` in both `readme.txt` and the `unipixel.php` header so WordPress blocks with a clear message instead. Related: `$wpdb->prepare()` with `%i` needs WP 6.2+, but `Requires at least` is 5.0.

---

## Google

### G-001: Mutual-exclusion rule for client-side vs server-side per event

**Rule:** For most event types, Google permits **either** client-side (gtag) **or** server-side (Measurement Protocol) — **not both at the same time**. The exception is **Purchase**, where both client-side and server-side are allowed (and recommended for dedup via `transaction_id`).

**Why:** Google's GA4 attribution does not deduplicate non-purchase events the way Meta CAPI does — sending the same event from both sides counts it twice. Purchase is special-cased because GA4 uses `transaction_id` as a natural dedup key.

**Re-checked against Google's docs (2026-10-20):**
- **Measurement Protocol (UniPixel's server path): still true.** GA4 dedupes only `purchase`, on `transaction_id`, web streams only ([Analytics Help 12313109](https://support.google.com/analytics/answer/12313109)). No Google GA4 or MP page offers an `event_id` or other dedup key; blogs claiming GA4 dedupes on `event_id` cite nothing from Google. The `event_id` param UniPixel adds to MP events is just a custom parameter. MP changelog to 2026-02-17: no dedup change. Google calls MP finished (no deprecation planned) and points new server-to-server work at the Data Manager API.
- **Data Manager API is the exception.** GA4 dedupes any event sent through it with a `transactionId` against the tag's copy (first copy wins), but only for allowlisted properties (v1.6, 2026-05-07). Google Ads: tag and API copies dedupe on `transactionId` per conversion action, open to all accounts since 2026-02-10. Auth is a Google Cloud project with OAuth or a service account, not an API secret. Guides under `developers.google.com/data-manager/api/devguides/events/`.
- **Google tag gateway doesn't change this:** the browser still sends one stream, relayed through the site's own domain to `<tag-id>.fps.goog`. Site Kit ships it as a PHP relay (`gtg/measurement.php`), so no CDN is needed.

**History (Rohan, 2026-10-20):** learned the hard way. UniPixel was first built to send Google events from both browser and server, like Meta, and GA4 counted them twice. Either/or, with Purchase as the exception, was the eventual concession.

**How the plugin implements it:** on the Google Events page, each row's client and server checkboxes are an XOR pair (`data-nondedup`, `admin/js/admin-common.js`): ticking one unticks the other, on every row except Purchase. The per-event seed (`config/schema.php`), Google's Apply Recommended preset and the Conversions builder (`admin/js/ajax-conversion-groups.js`) all default non-Purchase events to client only.

**Gap: the guard is UI-only.** No save handler checks the rule (nor does the Google server listener), and the XOR only fires when a box is ticked, so any path that writes the flags directly can store both on. Cases:
- **Fixed 2026-10-20 (fix-plan #21):** a clean install set Google PageView to browser + server, and the setup wizard posted `pageview_send_serverside: 1`. Harmless until Google server-side is switched on; from then on every page view went both ways. Both now default to browser only (`config/schema.php`, `admin/js/google-setup-wizard.js`). Existing installs keep whatever they have.
- **Fixed 2026-10-21 (uncommitted, fix-plan #36):** every setup-page save wrote `pageview_send_clientside = 1` (`admin/handlers/handler-platform-settings.php` defaulted it to 1 when the form didn't send it, and the form never does), so a Google site set to server-only PageView went back to both on save. Now it's only written when posted.

**Other platforms:** Meta, TikTok, Pinterest, Microsoft do **not** have this restriction. They support browser + CAPI on every event, with `event_id` (Meta/TikTok/Pinterest) as the dedup key.

**Implications for testing:**
- Variants of Google-related flows can only test legal admin states. Trying to enable both for a non-purchase event should be blocked by the UI itself (worth its own scenario in `admin-pixel-config`).
- Purchase variants are the only Google flow where "both on" is a valid state.

---

_(No platform reports processed yet)_

## Microsoft

### MS-001: UET `ecomm_pagetype` is an enum; an invalid value throws in `bat.js` (VERIFIED 2026-10-15)

Allowed values: `home`, `searchresults`, `category`, `product`, `cart`, `purchase`, `other`. UniPixel's begin_checkout sends `'checkout'`, and `bat.js` throws `Uncaught ecomm_pagetype value must be one of the allowed values`, so the event never leaves the browser. UET's variable revenue key is `revenue_value` (maps to `gv`), not `revenue`. UET also auto-sends `pageLoad` on load, so an extra custom `PageView` event plus a CAPI `pageLoad` double-counts page views. Fix tracked in `projects/fix-plan-2.6.13.md` (#11, #31).

---

## Pinterest

### PIN-001: Custom event tier accepts only 6 sub-types — TERMINOLOGY QUIRK

**Source:** Pinterest Conversions API event-type spec
**Surface impact:** Bespoke event flow (centralised Event Manager + per-platform Custom Events table)

**The quirk:** Pinterest's "custom" event tier is itself a finite list, not a free-form name. Accepted values: `custom`, `lead`, `search`, `signup`, `view_category`, `watch_video`. Anything outside this set is dropped or ignored when sent as the event_name on a Pinterest Tag / CAPI call.

**Implication for our UX:** Bespoke Platform Event References for Pinterest are not truly free-form. A user who types `MyBespokeEvent` for Pinterest will see no data. Either:
- Validate the bespoke value on input against the 6 allowed sub-types and warn, or
- Restrict the dropdown to these 6 values when Pinterest is the active platform context.

**Cross-reference:** `event-terminology.md` § Platform-specific quirks.

### PIN-002: `event_id` passed in the callback slot, so browser/CAPI dedup never happens (VERIFIED 2026-10-15, OPEN)

Every client-side Pinterest call is shaped `pintrk('track', name, payload, { event_id: ... })`: `clientfirst-watch-and-send-pinterest.js` and all four `woocomm-hook-handling/client-side-send-*.php` (Purchase included). Pinterest's 4th argument is a callback, so the console shows `Pinterest Tag Error: Expected '%s' to be a %s, but found '%s' callback function [object Object]` and the `event_id` is dropped. Verified on updev: the pagevisit beacon carried `ed=%7B%7D` (empty event data). The server-side event does carry `event_id`, so Pinterest can't pair them, and conversions sent both ways are likely double-counted. Present since at least the repo's initial commit (2026-04-19).

**Fix:** put `event_id` inside the payload object (`pintrk('track', 'checkout', { event_id, value, currency, ... })`). While there, check payload keys against Pinterest's spec (`order_quantity`, `line_items`) rather than Meta-style `num_items` / `contents` / `content_ids`.

---

## Google

### G-003: GA4 config runs before consent, no Consent Mode signals (CLAUDE'S CLAIM, 2026-10-15; Rohan disputes the principles, 2026-10-21)

`js/pixel-google-gtag.js` calls `gtag('config', id, { send_page_view: false })` on every page load whenever Google is enabled, regardless of consent state, and nothing in the plugin ever calls `gtag('consent', ...)`. Observed on updev: `_ga` and `_ga_0DZLET9ZXH` cookies set before Accept (confounded by the theme's own GA tag, but the code path is clear). Consent gating only delays UniPixel's `page_view`, not the tag's cookies. This conflicts with the readme/marketing claim that tracking pauses until consent, and without Consent Mode v2 defaults Google limits ads measurement for EEA traffic.

**Fix direction:** when consent honouring is on, push `gtag('consent', 'default', {... 'denied'})` before config and `gtag('consent', 'update', ...)` from the consent popup's grant/revoke.

### G-004: Google server-side couldn't reach visitors whose browser blocked Google's tag (FIXED 2026-10-21, uncommitted)

**Cause:** GA4 needs a `client_id` on every server event, and UniPixel only got one from Google's tag (the `_ga` cookie, or `gtag('get')` in the browser). With the tag blocked:
- Page view and site events never reached the server: `resolveGoogleClientId()` (`js/clientfirst-watch-and-send-google-helper.js`) waited forever for a `gtag('get')` answer, because `window.gtag` is always UniPixel's stand-in and only Google's library answers. Observed in headless Chrome: still waiting after 8s.
- WooCommerce events went out with an empty `client_id`, which GA4 discards.

**Fix:** the browser helper stops waiting after 3s (`timeoutMs`) and falls back to the `_ga` cookie or `''` (observed: resolves after 3002ms). With no ID, the Google sender calls `unipixel_get_or_create_ga_client_id()` (`functions/unipixel-functions.php`), which creates an ID in Google's format and sets a host-only first-party `_ga` cookie: `GA1.1.<random>.<timestamp>`, two years, path `/`, Secure on HTTPS, SameSite=Lax.

**Google's tag adopts an existing `_ga`** (verified in headless Chrome on updev, 2026-10-21): a preset on `.local.site` was used as-is; a host-only preset was used and copied to `.local.site` with the same ID. On a fresh visit Google's tag sets `GA1.1.<random>.<timestamp>` on the parent domain (`.local.site`).

**Events built partway through a page:** once page output has started (`headers_sent()`, the norm on classic themes), the cookie can't be set. So `unipixel_ensure_ga_client_id_before_page()` (`functions/hooks.php`, on `template_redirect`) creates the ID at the start of product, checkout and order-received pages, covering View item, Begin checkout and Purchase. Small cost: a product page load for a visitor without a `_ga` cookie carries the cookie, and page caches usually won't store that load.

**Limits:** An unreadable `_ga` is left alone. IDs are only created after the sender's consent check. These visitors appear in GA4 without browser-only data such as traffic source and session.

### G-005: Measurement Protocol `client_id` must be `<number>.<number>` (VERIFIED 2026-10-21)

Google's validation server in strict mode (`validation_behavior: ENFORCE_RECOMMENDATIONS`) rejects anything else: "client_id ... has invalid format. It should be in <number>.<number> format." The default (relaxed) check lets it through, so a payload can pass validation and still never show up. UniPixel's Test Connection sent `unipixel.test_connection.<letters>.<time>`, and its event never reached DebugView.

**DebugView also needs `session_id` and `engagement_time_msec`** (observed 2026-10-21, dev property): with a valid client ID, an event with only `debug_mode: 1` never appeared, while one with `debug_mode: true`, `session_id` and `engagement_time_msec` showed within a minute. Test Connection now sends `<random>.<time>` plus those three params (`admin/handlers/handler-google-test-connection.php`, uncommitted), and its event arrived. Each test is a new client ID, so it shows as a new entry in DebugView's "Debug Device" dropdown and can take about a minute.

### G-002: GA4 event-name validation — INFORMATIONAL

**Source:** GA4 event naming docs
**Surface impact:** Bespoke event flow for Google.

**The rules:** GA4 event names must start with a letter, contain only letters / numbers / underscores, and not exceed 40 chars. Spaces, hyphens, and special characters are rejected.

**Implication:** Our canonical bespoke example `MyBespokeEvent` is technically valid (starts with letter, only letters). It violates GA4's snake_case style convention but is accepted. No validation needed — but worth a soft inline note where space allows.

**Cross-reference:** `event-terminology.md` § Platform-specific quirks.

---

## WordPress (host environment)

### WP-001: Themes that deregister `jquery` silently kill the entire watcher chain — FIELD INCIDENT (steelchief.com.au, found 2026-07-12)

**Source:** Live incident on steelchief.com.au — Meta Lead events at zero for ~a month while all other events kept flowing.

**The failure mode:** `unipixel-common` declares `jquery` (the WP core handle) as a dependency (added as the "immediate fix" for backlog #26, on the assumption "WooCommerce sites always have jQuery"). WordPress **silently drops any enqueued script whose dependency handle is unregistered** — no PHP error, no console error, no admin notice. All five `clientfirst-watch-and-send-*` watchers and `unipixel-consent` depend on `unipixel-common`, so one missing `jquery` handle removes the entire element/URL event chain (client-side fbq AND the server-side AJAX relay) from the page. Base pixel (`pixel-meta.js`, dep-free) and PHP-side WooCommerce CAPI hooks are unaffected — so PageView/Purchase/etc keep arriving and the outage looks event-specific rather than systemic.

**The steelchief specifics:** The theme runs `wp_deregister_script('jquery')` on every non-WooCommerce page (it ships its own jQuery under the handle `jquery-front`, loaded in the header — so plugin JS still *works at runtime*; only the dependency declaration breaks). Result: watchers alive on shop/product/cart/checkout pages, dead on the getprice/contact/landing pages — which are exactly and only where the two Lead rows (`#thankyousuccess`, `#bookCallFormSubmitted`) live. Lead died on both channels; ClickOpenConfigurator/ConfiguratorShownPrice survived via product-page configurator opens; AddPaymentInfo survived on checkout. Pre-2.6.6 plugin versions had no `jquery` dep (verified against the 2.6.1 snapshot in the website-sheds repo), which is why Lead historically worked on the same theme. Business leads were never lost (Zoho inserts continued ~15–30/day, verified in server logs) — only the ad-platform signal.

**Why our testing missed it:** updev and uphq themes keep the WP `jquery` handle registered, so the drop never reproduces there.

**Fix direction:** Backlog #26 proper fix (remove jQuery usage from front-end JS — it's only `$(document).ready()` and `$.post()` — then drop the dep). Interim options: depend on `jquery` only when `wp_script_is('jquery', 'registered')`, or a runtime `window.jQuery` check with no declared dep. Site-side workaround for affected themes: re-register the `jquery` handle as an alias of the theme's own copy instead of deregistering it.

**Resolution:** Fixed in 2.6.11 (staged 2026-07-12) — front-end JS fully de-jQueryed (`unipixelOnReady` / `unipixelAjaxPost` helpers), `jquery` removed from `unipixel-common`'s dependency array. Verified on updev with WP jQuery deregistered to reproduce the steelchief condition: full chain enqueues and fires cleanly. steelchief.com.au heals when it updates to 2.6.11 — no site-side change needed.

**Product hardening idea:** a diagnostics/Site Health check — "UniPixel front-end tracker did not load on this page (theme deregisters `jquery`)" — this failure is otherwise invisible on every surface (no console error, empty event log, config UI looks perfect).

### WP-002: Full-page caching could serve stale nonces (THEORY, not observed on any real site)

> **Status, corrected 2026-10-15:** only one piece is verified: WordPress rejects a `unipixel_track_nonce` older than 12-24h with `403 -1`. There is **no evidence** that any real UniPixel site serves cached pages old enough for this to happen. It was wrongly labelled "verified" and ranked as a top issue. Treat it as an unconfirmed theory until it's observed on a real cached site.

**Source:** Public forum thread "Security and caching considerations" (2026-09-08, unresolved), reproduced on updev.

**The failure mode:** every front-end AJAX endpoint (`ajax_data_for_server_event_{meta,pinterest,tiktok,google}`, `unipixel_ajax_server_event_microsoft`, `unipixel_log_client_event`) runs `check_ajax_referer()` against a nonce localized into the page HTML. WP nonces are valid for 12-24h. A page cache (LiteSpeed Cache default public TTL is 7 days, Cloudflare APO, host-level caches) keeps serving the old nonce, so once the cached HTML is over ~24h old, every server-side event from it returns `403 -1`. The browser pixel still fires; only CAPI dies, silently (the JS parses `-1` and logs "not sent" to the console logger only).

**Verified:** logged-out nonces built 0-12h and 12-24h old returned 200; 24-36h and 36-48h old returned `403 -1` (Pinterest listener and client-event logger). WooCommerce's `nonce_user_logged_out` filter only applies to `woocommerce*` actions, so the failure is purely age-based.

**Why it matters:** CAPI is the headline feature. A store on a cached host sees client events in Events Manager and no server events, with nothing in the admin to explain why. Top hypothesis in `projects/adoption-dropoff-investigation.md`.

**Fix direction (see also `projects/fix-plan-2.6.13.md` #25):** don't gate the public tracking endpoints on a page-embedded nonce (a nonce on a public, unauthenticated endpoint adds no CSRF protection anyway). Alternatives: fetch a fresh nonce via an uncached request before sending, or move to a REST route with origin/referrer checks plus the 2.6.12 payload cap and rate limiting.

### WP-003: Block themes run WooCommerce template hooks before `wp_enqueue_scripts` (VERIFIED 2026-10-15)

Block themes render the whole template (`get_the_block_template_html()`) before `wp_head`, so `woocommerce_before_single_product` and `woocommerce_thankyou` fire before `wp_enqueue_scripts` has run. Any `wp_add_inline_script()` / `wp_localize_script()` against a handle registered in `wp_enqueue_scripts` (e.g. `unipixel-common`) returns false silently: no notice, no output. Verified on Twenty Twenty-Five (`unipixel_common_registered: false` at both hooks). Classic themes fire these hooks after `wp_head`, so the same code works there (verified on `buildio2`: hook ran with `unipixel-common` registered, browser ViewContent fired for Meta/Pinterest/GA with `eid` matching the server event). Only block-theme stores are affected. Pattern for anything added from a template hook: if `!did_action('wp_enqueue_scripts')`, queue it and attach on `wp_enqueue_scripts` at a late priority.

### WP-004: "Non-blocking" `wp_remote_post` still blocks for the full round trip, up to 1s (VERIFIED 2026-10-15)

WP's Requests cURL transport floors any timeout to 1 second (`Requests/src/Transport/Curl.php`: "cURL requires a minimum timeout of 1 second") and `curl_exec` is synchronous even with `blocking => false`. Measured from the dev laptop: `blocking=false, timeout=0.1` waited 0.34s (Meta) and 0.71s (Microsoft), the same as `blocking=true`. So every "fire-and-forget" call made during page render adds its full network round trip to TTFB. Truly deferring a call needs `fastcgi_finish_request()` / `litespeed_finish_request()` on `shutdown`, or Action Scheduler.

### WP-005: WordPress 7 checkbox styles wipe Bootstrap's switch circle (VERIFIED 2026-10-21)

WordPress 7's `wp-admin/css/forms.css` sets `input[type="checkbox"]:checked { background: var(--wp-admin-theme-color) }`. The `background` shorthand resets `background-image`, which is how Bootstrap 5.3 draws the switch circle, so ticked UniPixel switches showed a plain blue pill. Fixed in `admin/css/admin.css` by putting back `background-image: var(--bs-form-switch-bg)` and `background-size: contain` on ticked `.form-switch` inputs (uncommitted). Checked in headless Chrome and on the real Google setup page.

## Consent vendors

### CONSENT-001: Third-party CMP cookie formats (VERIFIED 2026-10-15)

- **OneTrust** (`OptanonConsent`): `groups=C0001:1,C0002:1,C0003:1,C0004:0,C0005:0`. Standard IDs: C0001 Strictly Necessary (always 1), C0002 Performance, C0003 Functional, C0004 Targeting (= marketing), C0005 Social Media. Stores can rename groups, so IDs should be configurable. UniPixel maps C0001 to marketing (bug, fix-plan #3).
- **Cookiebot** (`CookieConsent`): a JavaScript object literal, not JSON: `{stamp:'...',necessary:true,preferences:true,statistics:true,marketing:true,method:'explicit',ver:1,...}`. `JSON.parse` / `json_decode` fail on it; parse with a regex (fix-plan #5).
- **Moove GDPR** (`moove_gdpr_popup`): URL-encoded JSON (`{"strict":"1","thirdparty":"1","advanced":"0"}`); decode it as JSON, don't split on `,`/`:`.
