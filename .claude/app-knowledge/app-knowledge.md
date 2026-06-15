# App Knowledge — Plugin architecture, dev flow, testing

How the UniPixel plugin is built and how we develop against it.

For deployment and release (rsync, obfuscation, version bumping, wp.org SVN) see `deploy-and-release.md`.

---

## Stack

- **Language:** PHP 8.x (plugin declares `Requires PHP: 7.0` for broad compatibility, but our dev + dev.unipixelhq.com run 8.3).
- **Platform:** WordPress 5.0+ (tested up to 6.9.4). WooCommerce integration is the largest surface.
- **Frontend JS:** vanilla + jQuery for `$(document).ready()` and `$.post()` only. Frontend pixels (fbq, gtag, ttq, uetq, pintrk, pagView) are injected via `wp_add_inline_script` or via `woocommerce_add_to_cart_fragments` for AJAX flows.
- **Admin JS:** vanilla + jQuery.
- **Local dev host:** Laragon (XAMPP `htdocs`) at `https://updev.local.site`. Vhost and hosts entry already set up.
- **Remote dev host:** `https://dev.unipixelhq.com` — same plugin code, a dev box for sanity checks before obfuscation.

---

## Plugin folder layout

```
plugins/unipixel/
├── unipixel.php                  ← Main plugin file. Plugin header + UNIPIXEL_VERSION constant + require_once chain.
├── readme.txt                    ← wordpress.org plugin readme. Stable tag lives here.
├── CLAUDE.md                     ← Breadcrumb. Docs live at repo root, not here.
├── admin/                        ← All wp-admin UI for the plugin.
│   ├── handlers/                 ← Admin AJAX handlers, form processors.
│   ├── inc/                      ← Admin page includes (setup, events, settings per platform).
│   ├── js/                       ← Admin-side JS (event settings forms, "apply recommended" presets).
│   ├── css/, img/, vendor/
│   └── page-*.php                ← Individual admin screens (page-microsoft-setup.php, page-microsoft-events.php, etc.)
├── assets/                       ← Static assets excluded from obfuscation (images, sample data, anything not code).
├── classes/                      ← PHP classes. e.g. class-unipixel-log.php (stored event logs), fragment collector.
├── config/                       ← Schema + config. schema.php defines the events table (dbDelta).
├── css/                          ← Frontend CSS (consent popup, etc.)
├── functions/                    ← Core plugin functions.
│   ├── unipixel-functions.php    ← Big utility file. Shared helpers (user identifier, fbc value getter, etc.)
│   ├── hooks.php                 ← WordPress hook registrations, click-ID capture (capture_fbclid, etc.)
│   ├── send-server-event.php     ← Platform-agnostic server event dispatch.
│   ├── send-server-event-handle-result.php
│   ├── ajax-handle-log-client-event.php ← AJAX handler for client-first events.
│   └── consent.php               ← CMP reading + consent state gating.
├── js/                           ← Frontend pixel JS per platform.
│   ├── pixel-meta.js, pixel-microsoft.js, pixel-tiktok.js, pixel-pinterest.js, pixel-google.js
│   ├── clientfirst-watch-and-send-microsoft.js (+ per-platform variants)
│   ├── unipixel-consent.js
│   └── unipixel-common.js        ← Entry point. jQuery dep added automatically.
├── trackers/                     ← Per-platform server-side pipeline.
│   ├── microsoft-handler.php, microsoft-enqueue.php
│   ├── meta-ajax-listener-send-server.php
│   └── (equivalent per platform)
└── woocomm-hook-handling/        ← WooCommerce event pipeline.
    ├── hook-handlers-purchase.php, hook-handlers-addtocart.php, hook-handlers-checkout.php, hook-handlers-viewcontent.php
    ├── prepare-common-to-platform-purchase.php (+ per-event prepare files)
    ├── client-side-send-addtocart.php, client-side-localize-addtocart.php
    ├── get-common-woo-data-purchase.php (+ per-event getters)
    └── (16 files updated during Microsoft CAPI work in v2.6.0)
```

---

## Event pipeline — two patterns

Every UniPixel event follows one of two patterns. Which pattern is used is determined by whether the event originates server-side (driven by WooCommerce hooks) or client-side (driven by a page load or user interaction).

### Server-first (WooCommerce events)

Events: `Purchase`, `AddToCart`, `InitiateCheckout`, `ViewContent`.

```
WooCommerce hook fires (PHP)
  → hook-handlers-<event>.php picks it up
  → get-common-woo-data-<event>.php pulls order/product/cart data
  → prepare-common-to-platform-<event>.php shapes payload per platform
  → functions/send-server-event.php makes CAPI call (per platform, per toggle)
  → eventId generated as purchase_<microtime> in PHP
  → client pixel script injected via wp_add_inline_script OR fragment (AJAX add-to-cart)
  → browser pixel fires with SAME eventId
  → platform dedups via matching eventId
```

### Client-first (PageView + custom events)

Events: `PageView`, any custom click/view event.

```
Page loads / click happens (JS)
  → pixel-<platform>.js calls fbq() / ttq.track() / uetq.push() / gtag() / pintrk()
  → eventId generated as event_<timestamp> in JS
  → AJAX POST to admin-ajax.php (action: unipixel_log_client_event)
  → functions/ajax-handle-log-client-event.php receives, calls send-server-event.php
  → server-side CAPI call fires with SAME eventId
  → platform dedups
```

### AJAX add-to-cart (fragment pattern)

The WooCommerce AJAX add-to-cart flow is special — the hook fires inside an AJAX JSON response, so `wp_add_inline_script` does nothing. The `UniPixel_AddToCart_Fragment_Collector` class accumulates platform pixel calls during the request, and the `woocommerce_add_to_cart_fragments` filter injects them into the JSON as HTML fragments. WooCommerce's JS applies fragments to the DOM and the pixel fires. See `domain-knowledge/platform-discoveries.md` § ATC-001 for full context.

---

## Key conventions

### eventId

- **Server-first:** generated once in PHP as `<event>_<microtime>` (e.g. `purchase_1773473275.039`). Passed to both the CAPI call and the browser pixel via `wp_localize_script`. Same value reaches both.
- **Client-first:** generated once in JS as `event_<timestamp>`. Passed to `ttq.track()` / `fbq('track')` / etc. AND to the AJAX payload → server uses the same value for its CAPI call.

**Invariant:** both sides of an event MUST share the same eventId or platforms double-count.

### User identifier for transients

- Logged-in users: `get_current_user_id()`.
- Guests: `md5(IP + User-Agent)`.
- Always use `unipixel_get_user_identifier_for_transient()` — never inline the logic. Consolidated across the plugin during the AddToCart improvement. Known residual collision risk documented in `domain-knowledge/platform-discoveries.md` § ATC-002.

### Click IDs

- Capture from `$_GET['<clid>']` on `init` hook (`functions/hooks.php`).
- Currently a single first-party cookie (e.g. `unipixel_fbclid`), 90-day retention.
- Multi-tier persistence is planned — see `projects/multi-tier-clickid-persistence.md`.

### Consent

- `functions/consent.php` handles CMP reading (9 integrations) or own popup.
- Most event functions accept `$consentAlreadyChecked` as a parameter to avoid double-checking in deep layers.
- Argument ordering in `unipixel_send_server_event_*()` is: ..., `$pageUrl`, `$sendServerLogResponse`, `$consentAlreadyChecked`. **Don't misorder these** — see `domain-knowledge/platform-discoveries.md` § TT-002.

### Platform event naming

Each platform uses different event names for the same concept. Full table in `domain-knowledge/vocabulary.md`.

---

## Dev workflow

### Where to develop

Plugin source at `C:\xampp\htdocs\updev\public_html\wp-content\plugins\unipixel\`. Edits take effect immediately on `https://updev.local.site`.

### Editing PHP

- Changes show on next page load. No build step.
- **Watch for smart quotes** — pasting from ChatGPT, Google Docs, etc. regularly introduces U+2018/U+2019/U+201C/U+201D. These cause PHP parse errors in strings and signal contamination in comments. Pre-export checklist catches them; better to avoid introducing them.

### Editing JS / CSS

- No bundler. Files are loaded directly.
- Enqueued via `wp_enqueue_script()` in `trackers/*-enqueue.php` and `functions/hooks.php`.
- Frontend scripts use jQuery (dependency auto-loaded).
- **Hard-refresh after CSS / JS edits during development.** Assets are enqueued with `UNIPIXEL_VERSION` as the cache-bust query string. Until that version is bumped (release-gate), edits don't change the URL, and browsers serve the cached file on a normal reload. `Ctrl+F5` (Win) / `Cmd+Shift+R` (Mac) forces revalidation and picks up the new content. If you spend more than a minute thinking "my CSS change isn't taking effect", check the browser cache before suspecting the code.

### Testing locally

1. Ensure WooCommerce is active with at least one purchasable product.
2. Enable a non-card payment method (Check payments or Cash on Delivery) — avoids real payment processing.
3. Configure UniPixel with at least one platform (pixel ID + access token).
4. Full end-to-end test flow below.

### Testing remotely on dev.unipixelhq.com

1. rsync deploy from local (see `deploy-and-release.md`).
2. Load the site, walk the same test flow.
3. Confirms the plugin works in a real PHP environment outside Laragon before obfuscation.

---

## End-to-end test flow

Run after any significant change. Uses a local product page as the anchor; equivalent flow works on `dev.unipixelhq.com` with a matching fixture product.

### Test product

Low-price item (~$1) to minimise test order impact. On the old sheds dev site this was the Steel Dog Kennel Gable Roof (small variant).

### Test steps

1. **Product page** — PageView (client-first) + ViewContent (server-first) fire. Confirm in browser console and check `window.UniPixelViewContent<Platform>` exists with matching `event_id` across platforms.
2. **Custom click event** — click a configurator/CTA button. Confirm custom event fires client-first via `ttq.track()`, `uetq.push()`, `gtag()` etc. AJAX callback to `admin-ajax.php` relays to server.
3. **Add to Cart** — select required options, click Add to Cart. Confirm `window.UniPixelAddToCart<Platform>` exists with matching `event_id`. For AJAX add-to-cart flows (most themes, shop/archive pages), fragment mechanism injects pixel scripts.
4. **Checkout** — proceed to checkout, fill billing details. Confirm `window.UniPixelInitiateCheckoutTikTok` / `window.UniPixelCheckoutMicrosoft` exist (TikTok uses `InitiateCheckout`, Microsoft uses `begin_checkout`).
5. **Place order** — use test payment method. Land on order-received. Confirm `window.UniPixelPurchase<Platform>` with matching `event_id`, `value`, `currency`.
6. **Stored Event Logs** — wp-admin → UniPixel → Stored Event Logs. All events from the run should be present in order. Each WooCommerce event shows one row per platform, with `Send Method`, `Party`, and `Event Trigger` columns.

### What to check for

- **Dedup:** same `event_id` across all platforms for a given WooCommerce event.
- **Server responses:** server-side events should show `Successful: Code 200, Ok` (or `204`). Client-side always shows `Client-side event, no response` — that's normal, not an error.
- **Coverage:** missing platform rows for an event = enqueue or handler skipped. Check platform toggle settings + credentials.

### Quick smoke test (5 min)

1. Visit any product page → PageView fires for all platforms.
2. Click a configurator button → custom click event fires.
3. Stored Event Logs → events recorded.

Covers: pixel initialisation, client-first event pattern, custom event config, DB logging.

### Cleanup after testing

- Cancel test orders in WooCommerce → Orders (mark Cancelled or Trash).
- Optionally clear UniPixel stored event logs.

---

## Common pitfalls

- **Smart quotes in PHP** — see `domain-knowledge/platform-discoveries.md` § RQ-001. Scan before export.
- **Stray closing quotes in multiline PHP strings** — pass `php -l` on source, fail on stdin after obfuscation. See § RQ-002.
- **Argument ordering in send-server-event functions** — see § TT-002.
- **`wp_add_inline_script` in AJAX context** — does nothing. Use fragments. See § ATC-001.
- **Guest transient collisions** — `md5(IP+UA)` keys can collide behind NAT. Use `unipixel_get_user_identifier_for_transient()` and prefer WC session where possible. See § ATC-002.
- **Trusting AI-summarised API URLs** — verify endpoints with direct `curl` before coding against them. The Google `/_debug_/mp/collect` vs `/debug/mp/collect` case (Phase 7 token-acquisition-ux work) cost us an extra round of debugging because a WebFetch summary inserted underscores that aren't in the real URL.
- **Bootstrap popover sanitizer strips data attributes from inner links** — `data-bs-toggle="modal"` inside popover HTML content is silently stripped by default. Use an href-anchor pattern (e.g. `href="#some-modal"`) and document-level click delegation in the consuming JS instead. See Phase 6 of the token-acquisition-ux project doc for the worked example.
- **Unguarded `$_POST` reads in AJAX handlers leak warnings into the JSON response** — a `$_POST` key read without `isset()` throws an Undefined-array-key warning on PHP 8; on any install with `display_errors` / `WP_DEBUG_DISPLAY` on, that warning text prepends to the AJAX body and breaks `JSON.parse` (surfaces to users as "Ajax request failed: parsererror"). Guard every optional `$_POST` key. Root cause of the 2.6.9 `unipixel_meta` report; stayed silent on the dev boxes only because display is off there (warnings go to the log).
- **A save handler must only write the fields its own form controls** — the platform-settings save was unconditionally writing `pageview_send_serverside` (an *events*-page setting) on every save, defaulting it to 0 and silently undoing the user's choice. Build `$update_data` conditionally; only add a column when the submitting form actually posted it.
- **Hoverable popovers need a hide delay + tip-bound handlers** — Bootstrap hover popovers hide on the icon's mouseleave before the cursor can reach links/text inside. Use `trigger:'manual'`, a ~250ms hide delay, and bind mouseenter (cancel-hide) / mouseleave (schedule-hide) on the tip via the `shown.bs.popover` event. Grabbing the tip synchronously right after `show()` (`_popover` / `querySelector('.popover')`) is unreliable. See `admin/js/admin-common.js`.

---

## Test Connection pattern (per-platform)

Each platform's setup page has a Test Connection button (in `admin/handlers/handler-{platform}-test-connection.php`) that validates the credentials live against the platform's API. Pattern shipped during the token-acquisition-ux project:

- **Format checks first.** Cheap regex on Pixel ID + Access Token shape catches paste errors / wrong-platform IDs before any API call. Each platform's format is different (Meta Pixel IDs are 14-17 digits, Google Measurement IDs start with `G-`, TikTok Pixel IDs are 20 uppercase alphanumeric, Pinterest Tag IDs and Ad Account IDs are numeric, Microsoft UET Tag IDs are 7-9 digits).
- **API call to a validation endpoint where one exists.** Meta has `debug_token` (real token validation). Pinterest has `/v5/user_account` and `/v5/ad_accounts/{id}` for token + account check. TikTok and Microsoft don't have credential-validation endpoints, so we fire a real test event to their production endpoint (TikTok with `test_event_code` to land in Test Events tab; Microsoft to CAPI endpoint and parse the structured error response). Google's debug endpoint doesn't validate the API Secret value at all, so we send a `debug_mode:1` event to production so it lands in GA4 DebugView for user self-verification.
- **Parse platform-specific diagnostic feedback** into actionable user messages. Meta has the richest signal (codes 190 + subcodes 460/463/464 for token issues, 100 for parameter problems, 200/220 for permission, plus scopes/expires_at in `data`). TikTok soft errors are message-keyword-matched. Pinterest and Microsoft use HTTP status + JSON `error.code/message`.
- **Record successful Test Connection timestamp** to `unipixel_test_connection_timestamps` (WP option, `platform_id => unix_time`). The persistent status indicator strip and home dashboard badge read this to flip Connected immediately (rather than waiting for real events to flow). Helper: `unipixel_get_platform_connection_state($platform_id)`.

## Connection state machine (3 states, platform-agnostic helper)

`unipixel_get_platform_connection_state($platform_id)` in `functions/unipixel-functions.php` returns `{state, last_test_at, last_event_at}`. States:

- **not_started** — credentials (Pixel ID + Access Token) not both present.
- **pasted_unverified** — credentials present, but no recent successful Test Connection AND no successful server event in `unipixel_event_log` with `method='server'` AND `response_message LIKE '%Successful%'` in the last 24h.
- **connected** — credentials present, AND (recent successful Test Connection OR ≥1 successful event in 24h).

Reused by setup-page status strip, home dashboard badges. Hybrid definition deliberate: pure data-driven definition would leave the strip amber after a Test Connection pass until events actually fired. See `projects/token-acquisition-ux.md` for design rationale.

## Wizard component structure

Each platform setup page has a Bootstrap modal-lg wizard (`#{platform}-setup-wizard-modal`) with 7 steps: What you'll achieve / Prerequisites / What to ignore / Get your credentials / Paste and save / Test connection / Done. Step navigation in `admin/js/{platform}-setup-wizard.js`. Common patterns:

- **Modal trigger** via `data-bs-toggle="modal"` on Start Setup button (visible when state is `not_started`) and Re-walk Setup link (visible in `pasted_unverified` + `connected`).
- **Help-icon popover links** open the wizard too via document-level delegation on `a[href="#{platform}-setup-wizard"]`. Survives Bootstrap's popover sanitizer (see pitfalls above).
- **Input sync** between wizard inputs and underlying page form inputs on modal open + on save.
- **Step 5 Save** uses the existing `unipixel_update_platform` AJAX (platform-agnostic). Step 6 Test Connection uses the platform-specific `unipixel_{platform}_test_connection` action. Step 7 Done reloads the page so the strip flips green.
- **"Looks different in your dashboard?" link** in every step closes the wizard and opens the existing `#unipixelFeedbackModal` (rendered globally by `inc/feedback.php`) with the description pre-filled as `{Platform} server-side wizard, step N (step title):` so the user just adds their description and submits.

## Plain-English event log labels

`unipixel_classify_event_log_response($response_message, $method)` in `functions/unipixel-functions.php` maps raw `response_message` strings to `{label, level, bootstrap_class, raw}`. Used by the Stored Event Logs page to render badges instead of raw HTTP codes. HTTP code extraction (Code XXX / JSON code field / standalone 3-digit) plus keyword fallback. `method='client'` rows get a special "Client-side, no response" badge so users don't think those are failures. Empty or "logging turned off" rows get muted "Not logged".

## Stored event log auto-prune

`UniPixelLog::insert_log()` (`classes/class-unipixel-log.php`) writes each stored event and keeps a single gauge row in `unipixel_log_count`. When the gauge hits the trigger it calls `cleanup_logs()` to delete the oldest rows (ordered by `log_time`). Thresholds as of 2.6.9: **trigger 20k, delete oldest 10k**, so the table sawtooths between ~10k and 20k.

- The gauge is **internal-only** (never displayed — the admin pages count rows directly). After a prune it is set to the actual `COUNT(*)`, so it stays in lockstep with reality and the next trigger fires at a genuine row count.
- **History / gotcha:** through 2.6.8 the prune was *completely inert*. `cleanup_logs()` passed the ID array to `wpdb::prepare()` as a single positional arg against N `%d` placeholders; the placeholder/arg counts never matched, so `prepare()` returned an empty string and the DELETE did nothing. The log grew unbounded (driving slow page renders on bloated installs). Fixed in 2.6.9 by spreading the IDs (`...$ids`). Lesson: `wpdb::prepare()` only array-unwraps when the array is the *sole* arg — mix a scalar + an array and it silently mismatches.
- **The `log_time` index (2.6.8 hotfix) is largely cosmetic for the prune itself.** MySQL only uses it when `LIMIT` is a small fraction of the table; at the prune's `LIMIT 10000` it ignores the index and filesorts (verified: used at LIMIT 10/100, ignored at LIMIT 1000/10000 on a 40k table). Once the prune actually works the table is capped at 20k, so the filesort is milliseconds regardless. The index's real value is the admin Stored Event Logs viewer's small-`LIMIT` paginated queries. **The working prune, not the index, is what prevents the bloat-driven slow renders.**
