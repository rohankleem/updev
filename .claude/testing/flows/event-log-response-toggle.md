# Flow: send_server_log_response toggle

**Status:** Draft
**Last run:** —
**Covers:** The `send_server_log_response` toggle and what it changes about the `wp_unipixel_event_log` row. This is the verification-trap toggle: when off, the plugin fires-and-forgets and the event log captures dispatch but not platform acceptance. Tests that need to confirm "platform accepted the CAPI call" must set this on, otherwise they get false confidence from a populated row that says nothing about platform response.

This flow exists to document the contract clearly, in code-runnable form, so future regressions in the dispatch/log behaviour are caught.

## Setup

- WooCommerce store with at least one purchasable product
- Meta has valid pixel ID and CAPI access token entered (any platform works; Meta is convenient because match-quality response messages are detailed)
- Meta `platform_enabled = 1, serverside_global_enabled = 1`
- Meta Purchase row: `event_enabled = 1, send_client = 0, send_server = 1` (server-only, so the only path under test is server-side)
- DB store toggles on: General Settings → `dbstore_woocommerce_events.purchase = 1`
- Plugin's console logging on
- Baseline: per `baseline-state.md`

## Scenario 1: `send_server_log_response = 1` populates `response_message`

When the toggle is on, the plugin waits for the platform's HTTP response and stores it in `wp_unipixel_event_log.response_message`.

**State delta from baseline:**
- Meta Purchase row: `send_server_log_response = 1` (in addition to setup defaults)

**Action:** Complete a WooCommerce purchase.

**Asserts:**
- A row exists in `wp_unipixel_event_log` with `platform_name = 'Meta', event_name = 'Purchase', method = 'server', party = 'capi'` (or whatever this codebase uses for the server-side party label) for the run window
- That row's `response_message` column is **populated and non-empty**
- The response is parseable JSON containing Meta's standard CAPI response shape (e.g. `events_received`, `messages`, `fbtrace_id`)
- HTTP request from the WordPress server to `graph.facebook.com/v.../events` was made (visible in PHP error log if Meta access tokens are wrong, otherwise inferred from successful response)

**Captures:**
- The full row from `wp_unipixel_event_log` for this purchase → `expected/scenario-1-purchase-row.json`
- The parsed `response_message` content → `expected/scenario-1-meta-capi-response.json`

---

## Scenario 2: `send_server_log_response = 0` leaves `response_message` empty

When the toggle is off, the plugin fires-and-forgets. The row appears in the log but `response_message` is null/empty. Visitors looking at the Stored Event Logs page see "this fired" but no platform feedback.

**State delta from baseline:**
- Meta Purchase row: `send_server_log_response = 0`

**Action:** Complete a WooCommerce purchase.

**Asserts:**
- A row exists in `wp_unipixel_event_log` with `platform_name = 'Meta', event_name = 'Purchase', method = 'server'` for the run window
- That row's `response_message` is **null, empty string, or otherwise empty** (capture exact behaviour for the contract)
- The `sent_data` column is still populated (the plugin still records what it sent, just not what came back)
- The server still made the HTTP request to Meta (the request leaves the server even if we don't wait for the reply). Confirm via PHP error log or webhook listener if a separate verification surface exists; this assert is best-effort.

**Captures:**
- The row from `wp_unipixel_event_log` for this purchase → `expected/scenario-2-purchase-row.json`
- Confirm `response_message` shape (null vs `''` vs absent) for the contract → record in run log

---

## Scenario 3: Toggle does not affect dispatch, only logging

The contract: this toggle is about waiting for the response and storing it. It is **not** a dispatch toggle. The HTTP request to the platform still goes out either way. This scenario confirms that.

**State delta from baseline:**
- Run scenario 1 and scenario 2 in sequence, then compare the dispatch behaviour

**Action:** Compare server-side outbound HTTP traffic between scenario 1 and scenario 2 runs.

**Asserts:**
- Both runs produce an outbound HTTP POST to the Meta CAPI endpoint
- The request payload (`sent_data` column) is identical in shape between the two runs (modulo timestamp/event_id which vary per run)
- The only difference is whether `response_message` was populated

**Captures:**
- The `sent_data` from scenario 1 and scenario 2 → diff should show only `event_time` and `event_id` differences

---

## Notes for the runner

- This is the toggle most likely to cause a tester to declare a passing test that shouldn't pass. If a flow is verifying "Meta accepted the CAPI call" but `send_server_log_response = 0`, the row's mere existence proves dispatch but not acceptance. Always check `send_server_log_response` is on for any acceptance-checking flow.
- Performance note: with `send_server_log_response = 1`, the server waits for the platform reply before completing the request. This adds latency to every event. The toggle being off by default for ViewContent (per `app-knowledge`) is intentional. Tests should not assume on-by-default.
- This flow is per-platform-per-event scoped. The toggle is per-row in `wp_unipixel_woocomm_event_settings`. If a regression breaks the toggle for one platform but not another, this flow only catches it for Meta. Worth running against at least one other platform on a release-gate basis.
