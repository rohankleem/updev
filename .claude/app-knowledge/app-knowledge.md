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
