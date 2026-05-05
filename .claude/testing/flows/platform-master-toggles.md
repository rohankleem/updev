# Flow: Platform master toggles

**Status:** Draft
**Last run:** —
**Covers:** The two master switches at the platform level (`platform_enabled` and `serverside_global_enabled`) and how they override per-event flags. Verifies that flipping either master off suppresses every event for that platform regardless of `send_client` / `send_server` per-event values.

These are the highest-blast-radius regressions: a code path that reads per-event flags without consulting the platform-level master will silently fire events when it shouldn't.

## Setup

- WooCommerce store with at least one purchasable product and a working checkout (Stripe test mode or equivalent)
- All five platforms have valid pixel IDs and CAPI tokens entered (so credential checks don't suppress events for an unrelated reason)
- Plugin's console logging on: General Settings → `enableLogging_SendEvents = 1`
- DB store toggles on: General Settings → `dbstore_woocommerce_events[*] = 1` (so events appear in the log we read for verification)
- Baseline state: per `baseline-state.md` (or fresh-install defaults until that file is locked down)

## Scenario 1: `platform_enabled = 0` suppresses every event for that platform

Confirms the platform-level master overrides every per-event flag. Flipping Meta off with all per-event flags WANTING to fire should still produce zero Meta traffic.

**State delta from baseline:**
- Meta: `wp_unipixel_platform_settings.platform_enabled = 0`
- Meta: every row in `wp_unipixel_woocomm_event_settings` has `send_client = 1, send_server = 1, send_server_log_response = 1`
- All other platforms: baseline (enabled, default per-event flags)

**Action:** Visit a single product page, add to cart, complete a test checkout. Triggers the full WC event sequence (PageView, ViewContent, AddToCart, Checkout, Purchase).

**Asserts:**
- Zero network requests to `graph.facebook.com/*` or `connect.facebook.net/.../tr` for the run
- Zero rows in `wp_unipixel_event_log` with `platform_name = 'Meta'` for this run window
- No `fbq('track', ...)` calls captured in the network log
- Other platforms still fire normally (Google, TikTok, Pinterest, Microsoft each have at least one row in event_log)

**Captures:**
- Network log filtered for `facebook.com` and `facebook.net` (should be empty) → `expected/scenario-1-meta-network.json`
- `wp_unipixel_event_log` rows for the run window → `expected/scenario-1-event-log.json`

---

## Scenario 2: `serverside_global_enabled = 0` makes per-event `send_server` a no-op

Tests the server-side master overriding the per-event server flag.

**State delta from baseline:**
- Meta: `platform_enabled = 1, serverside_global_enabled = 0`
- Meta Purchase row: `send_server = 1, send_client = 1, send_server_log_response = 1`
- All other settings: baseline

**Action:** Complete a WooCommerce purchase.

**Asserts:**
- Browser-side Meta Purchase fires (network request to `connect.facebook.net/.../tr` or `facebook.com/tr`)
- No CAPI request originates from the server to `graph.facebook.com/v.../events`
- No row in `wp_unipixel_event_log` with `platform_name = 'Meta', method = 'server', event_name = 'Purchase'`
- A row exists with `method = 'client'` for Meta Purchase

**Captures:**
- Event log filtered to Meta + Purchase + this run → `expected/scenario-2-meta-purchase-log.json`

---

## Scenario 3: `platform_enabled = 0` overrides `send_client = 1`

The master-overrides-per-event direction for client-side. Confirms that `platform_enabled = 0` suppresses browser pixel calls even when `send_client = 1`.

**State delta from baseline:**
- TikTok: `platform_enabled = 0`
- TikTok ViewContent row: `send_client = 1, send_server = 1, send_server_log_response = 1`
- All other settings: baseline

**Action:** Visit a single product page (triggers ViewContent on a properly-configured store).

**Asserts:**
- No `ttq` calls in the network log
- No requests to `analytics.tiktok.com/api/v2/pixel`
- No rows in `wp_unipixel_event_log` for `platform_name = 'TikTok', event_name = 'ViewContent'`

**Captures:**
- Network log filtered for `tiktok.com` (should be empty) → `expected/scenario-3-tiktok-network.json`

---

## Scenario 4: All on (control)

Sanity-check that with everything enabled, the test environment produces the expected baseline behaviour. If this scenario fails, scenarios 1-3 negative results are not trustworthy (they could be passing for the wrong reason).

**State delta from baseline:**
- All platforms: `platform_enabled = 1, serverside_global_enabled = 1`
- For each WC event row, all platforms: `send_client = 1, send_server = 1, send_server_log_response = 1`
- **Exception**: Google's non-Purchase events must respect G-001 (see `google-g001-mutex.md`). For this scenario, set Google non-Purchase events to `send_client = 1, send_server = 0` to keep the test legal.

**Action:** Complete a WooCommerce purchase.

**Asserts:**
- All five platforms have at least one row in `wp_unipixel_event_log` for the run
- Network requests captured for each platform's event endpoint
- Dedup `event_id` matches between client-side and server-side rows for Meta, TikTok, Pinterest, Microsoft (G-001 prevents both for Google non-Purchase, so dedup check there is Purchase-only)

**Captures:**
- Full event log for the run, sorted by platform → `expected/scenario-4-all-platforms-log.json`
- Sample full payload per platform (one per platform) → `expected/scenario-4-{platform}-payload.json`

---

## Notes for the runner

- This flow only tests the on/off mechanics of the master switches. It does not test the shape of events when they DO fire (covered by `woocommerce-purchase`, `woocommerce-add-to-cart`, `woocommerce-viewcontent`)
- **Restore after each scenario is critical**: a leftover `platform_enabled = 0` state will silently break subsequent flows. The mysqldump-snapshot-restore pattern in `testing.md` § Pattern for variant setup is mandatory here.
- Snapshot the affected tables before scenario 1, restore between scenarios 1-3, and again after scenario 4.
- The `additional_id` field on each platform setup page is irrelevant to this flow (it's a secondary pixel ID, not a master switch).
