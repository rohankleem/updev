# Testing — methodology + index

How we verify the UniPixel plugin behaves correctly. Lives separately from app-knowledge (how it's built) and domain-knowledge (what it's about) — testing is its own thing.

The browser agent (Claude in Chrome) executes most flows directly. Things that need platform login (Meta Events Manager, GA, etc.) are flagged as human spot-checks.

## Verification surfaces

What the agent can read to verify behaviour, in order of preference:

1. **Browser-side state** — `window.fbq` / `gtag` / `ttq` / `pintrk` defined, `dataLayer` contents, cookie values (e.g. `unipixel_consent_summary`, `unipixel_fbclid`), localStorage. Read via `javascript_tool`.
2. **Network requests** — pixel beacons going to platform endpoints (`graph.facebook.com/tr`, `analytics.tiktok.com/api/v2/pixel`, etc.). Read via `read_network_requests`.
3. **Browser console** — when the plugin's console logging is enabled, server-side event results are echoed to the browser console. Read via `read_console_messages`.
4. **Event Test Console page** — `wp-admin/admin.php?page=unipixel_console_logger`. Live admin page that mirrors what events fired browser- and server-side. Navigate + read DOM.
5. **Stored Event Logs (DB)** — wp-admin page that surfaces the persistent log of all events sent server-side, including platform responses. Most reliable record of CAPI traffic. Backed by `wp_unipixel_event_log`. Note: the **`response_message` column is only populated when `send_server_log_response = 1`** in `wp_unipixel_woocomm_event_settings` for that platform/event row. When OFF, the plugin fires-and-forgets and the log captures dispatch but not platform acceptance. Tests that need to verify "platform accepted the CAPI call" must ensure this flag is on.
6. **Platform debug tools** — Meta Events Manager test events, GA DebugView, TikTok Pixel Helper. Human spot-check only (need login).

For most flows, layers 1–5 give complete confidence. Layer 6 is sampled monthly, not per-run.

## Pre-run checklist (agent)

Before running any flow that involves CAPI or server-side events, the agent should ensure the plugin's console logging is on (via wp-admin, plugin settings) so server results echo to browser console. Note in the run log whether console logging was on.

---

## Manipulating plugin settings

Most flows depend on a specific admin configuration (which platforms are enabled, server-side vs client-side per platform, debug flags, consent popup style, etc.). The agent has two ways to set state:

| Approach | When to use |
|---|---|
| **WP CLI** (`wp option update unipixel_xyz value` via Bash) | Default. Setting up state before a flow. Fast, deterministic, idempotent, easy to restore. |
| **Direct DB write** (mysql query into `wp_options`) | Fallback if WP CLI isn't available. Same effect. |
| **Click through admin UI** (browser agent) | Only when testing the admin UI itself (e.g. `admin-pixel-config` flow). Otherwise too slow + fragile. |

### Setup state contract

The plugin is **DB-driven**: same browser action produces wildly different behaviour depending on platform/event toggles, send-mode flags, log-response settings, and consent state. A flow that doesn't declare its DB state isn't a deterministic test — it's a coincidence dependent on whatever's in the DB at run time.

**Every Active flow MUST declare its required DB state.** No exceptions. Draft flows can leave state TBD while being written, but they don't graduate to Active until state is explicit.

Each flow declares state as a **delta from baseline** (see below). The agent:

1. Snapshots affected tables (so we can restore).
2. Resets to baseline.
3. Applies the flow's delta.
4. Runs the flow.
5. Restores original snapshot.

### Baseline state

The reference "known good" configuration that flows declare deltas against. Defined once, lives at `.claude/testing/baseline-state.md` (created when we run our first flow and need to lock it down). Until that file exists, baseline = "fresh install defaults from `config/schema.php`" — i.e. the values the plugin sets on activation.

A flow's state section then reads like:

```markdown
**State delta from baseline:**
- `wp_unipixel_platform_settings.serverside_global_enabled = 1` for Meta
- `wp_unipixel_woocomm_event_settings.send_server = 1, send_server_log_response = 1` for (Meta, Purchase)
- All other settings: baseline
```

### Settings inventory grows on demand

A complete catalogue of every plugin setting (key, effect, default) belongs in `domain-knowledge/` and is built **incrementally** as features encounter settings — not pre-emptively. When a flow or feature first touches a setting, document that setting at the same time. This keeps the inventory accurate; pre-cataloguing produces bit-rot.

### Where settings actually live

The plugin uses **its own DB tables**, not (mostly) `wp_options`. To set state, target the right table.

| Table | What it holds | Key columns for testing |
|---|---|---|
| `wp_unipixel_platform_settings` | One row per platform (Meta, Google, TikTok, Pinterest, Microsoft) | `platform_enabled` (0/1), `pixel_id`, `access_token`, `pageview_send_clientside`, `pageview_send_serverside`, `serverside_global_enabled` |
| `wp_unipixel_woocomm_event_settings` | Per-platform × per-WC-event toggles (ViewContent, AddToCart, Checkout, Purchase) | `event_enabled` (0/1), `send_client` (0/1), `send_server` (0/1), `send_server_log_response` (0/1 — wait-for-response and store in event log) |
| `wp_unipixel_events_settings` | Custom events (user-defined element + trigger → event name) | `send_client`, `send_server` |
| `wp_unipixel_event_log` | Read-only for tests — the persistent log of dispatched events. Verification surface. | `platform_name`, `event_name`, `method`, `party`, `sent_data`, `response_message`, `log_time` |
| `wp_unipixel_log_count` | Counter — read-only for tests | `count` |
| `wp_options` (`unipixel_*`) | Plugin-wide settings: consent popup config, console logging on/off, debug flags | Various |

**To enable Google CAPI for Purchase only:**
```sql
UPDATE wp_unipixel_woocomm_event_settings
SET send_server = 1
WHERE platform_id = {google_id} AND event_local_ref = 'purchase';
```

**To turn off Meta entirely:**
```sql
UPDATE wp_unipixel_platform_settings
SET platform_enabled = 0
WHERE platform_name = 'Meta';
```

### Pattern for variant setup

```bash
# 1. Snapshot current state (so we can restore)
mysqldump -uroot updev wp_unipixel_platform_settings wp_unipixel_woocomm_event_settings > /tmp/state-before.sql

# 2. Apply variant state
mysql -uroot updev <<'SQL'
UPDATE wp_unipixel_platform_settings SET serverside_global_enabled = 1 WHERE platform_name = 'Google';
UPDATE wp_unipixel_woocomm_event_settings SET send_server = 0, send_client = 1 WHERE platform_id = {google_id} AND event_local_ref != 'purchase';
UPDATE wp_unipixel_woocomm_event_settings SET send_server = 1, send_client = 1 WHERE platform_id = {google_id} AND event_local_ref = 'purchase';
SQL

# 3. Run flow
# 4. Restore
mysql -uroot updev < /tmp/state-before.sql
```

### Discovering things on the fly

- Platform IDs: `SELECT id, platform_name FROM wp_unipixel_platform_settings;`
- Current WC event toggles: `SELECT * FROM wp_unipixel_woocomm_event_settings;`
- Plugin-level options: `wp option list --search="unipixel*"` (or `SELECT option_name, option_value FROM wp_options WHERE option_name LIKE 'unipixel%';`)

---

## Flow variants

A flow can have **variants** — same body, different admin state. Useful when one feature has a config matrix:

```markdown
## Variants

### Variant: full-dedup (default)
**State:** Meta CAPI on, browser on
**Expected outcome:** Both browser and CAPI events fire, event_id matches.

### Variant: server-only
**State:** Meta CAPI on, browser off
**Expected outcome:** No browser-side fbq calls, CAPI fires alone.

### Variant: meta-disabled
**State:** Meta off entirely
**Expected outcome:** No Meta events anywhere. Other platforms unaffected.
```

When the agent runs a flow, it specifies which variant (or "all"). The run log records which variant was tested. Captures and baselines are per-variant: `expected/variant-{name}/scenario-1-meta-payload.json`.

### Platform-specific constraints on variants

Not every (browser, CAPI) combination is legal across all platforms. When defining variants, check `domain-knowledge/platform-discoveries.md` for the relevant platform.

**Google (G-001):** Client-side OR server-side per event, never both — except Purchase, where both are allowed and recommended for dedup. The admin UI enforces this. Variants for Google flows must respect this rule; testing the UI's enforcement is its own scenario in `admin-pixel-config`.

**Meta, TikTok, Pinterest, Microsoft:** No mutual-exclusion. Browser + CAPI on every event with `event_id` (Meta/TikTok/Pinterest) as the dedup key. All four variants (browser-only / server-only / both / disabled) are valid for any event.

---

## Concepts

| Term | Definition |
|---|---|
| **Flow** | A user journey with a setup state and a sequence of steps. One file per flow. Examples: consent grant on first visit, click ID capture, WooCommerce purchase. |
| **Scenario** | A step inside a flow. Has an action and expected outcomes. Numbered within the flow. |
| **Check — Assert** | A specific contract. Pass/fail. Written explicitly in the flow file. Example: `event_name = "Purchase"`, `currency = "AUD"`. |
| **Check — Capture** | A baseline payload/state recorded once, diffed on future runs. Lives as a JSON file alongside the flow. Used for things too verbose to assert field-by-field (e.g. full Meta CAPI payload). |
| **Run** | One execution of a flow. Produces a dated results file in `runs/`. |
| **Baseline** | A captured fixture that has been reviewed and blessed. Future runs diff against it. |

---

## File layout

```
.claude/testing/
├── testing.md                            # This file. Methodology + index of all flows.
└── flows/
    ├── {flow-name}.md                    # Spec: setup, scenarios, asserts, captures.
    └── {flow-name}/                      # Per-flow folder for fixtures + run logs.
        ├── expected/
        │   └── {scenario}-{label}.json   # Blessed baseline.
        └── runs/
            └── {YYYY-MM-DD-HHmm}.md      # Run results: pass/fail per check + diffs.
```

A flow has its `.md` file. The folder of the same name holds fixtures and run logs — created on first run.

---

## Flow file format

```markdown
# Flow: {name}

**Status:** Draft / Active / Parked
**Last run:** {date or —}
**Covers:** Short description of what surfaces this flow exercises.

## Setup
- Preconditions (browser state, cookies, plugin config)
- Starting URL

## Scenario 1: {short name}
**Action:** What we do (click X, navigate to Y, run JS Z).

**Asserts:**
- Specific verifiable contracts. Pass/fail.

**Captures:**
- Things saved as fixtures and diffed next run. Reference filename in expected/.
```

---

## How a test run works

When asked to run a flow:

1. Read the flow file. Confirm setup preconditions are met (or set them up).
2. For each scenario in order:
   - Execute the action.
   - Run each Assert. Record pass/fail.
   - For each Capture: record the actual value/payload. If a baseline exists in `expected/`, diff against it. If not, mark `[NEEDS REVIEW: no baseline yet]`.
3. Write `flows/{flow-name}/runs/{YYYY-MM-DD-HHmm}.md` with:
   - Per-scenario pass/fail summary.
   - Full assert results.
   - Diff summaries for each capture.
   - Anything flagged for human review.
4. Surface anything failing or flagged in chat for the user to look at.

## How baselines get blessed

First run of a flow: every capture is `[NEEDS REVIEW: no baseline yet]`. The user eyeballs, says "those look right" — Claude moves them into `expected/`. Same dance after legitimate behaviour changes (new field added on purpose, payload structure changed). Baselines aren't sacred; they're a tool for catching unintended drift.

---

## When tests get added

- **Feature planning** — when designing a new feature in `projects/`, the design conversation produces a draft flow file in `flows/`. The flow is part of the feature spec, not a follow-up.
- **Bug fix** — if a bug slipped past the existing flows, add a scenario or a new flow that would have caught it. Then fix.
- **Release gate** — before each `_obf/` export, re-run all Active flows for affected areas. Results logged. Failing flows block release.

---

## Index of flows

| Flow | Status | Covers |
|---|---|---|
| [consent-grant-first-visit](flows/consent-grant-first-visit.md) | Draft | Banner appearance on first visit, Accept All, post-consent pixel firing, persistence across reload |
| [consent-decline-first-visit](flows/consent-decline-first-visit.md) | Draft | Reject All button suppresses all marketing pixels, persistence |
| [consent-revoke](flows/consent-revoke.md) | Draft | Granted user changes mind, marketing pixels stop firing on next pageview |
| [consent-granular](flows/consent-granular.md) | Draft | Adjust Preferences panel, granular category toggles, only chosen platforms fire |
| [click-id-capture](flows/click-id-capture.md) | Draft | URL click IDs (fbclid, gclid, ttclid, msclkid) captured into cookies, persist across pages |
| [woocommerce-viewcontent](flows/woocommerce-viewcontent.md) | Draft | Single product page → ViewContent fires browser + CAPI for each platform |
| [woocommerce-add-to-cart](flows/woocommerce-add-to-cart.md) | Draft | Add to cart → AddToCart fires browser + CAPI, dedup IDs match |
| [woocommerce-purchase](flows/woocommerce-purchase.md) | Draft | Order completion → Purchase fires browser + CAPI, value/currency correct, dedup |
| [admin-pixel-config](flows/admin-pixel-config.md) | Draft | Each platform settings page saves pixel ID, persists across reload |
| [custom-event-url-trigger](flows/custom-event-url-trigger.md) | Draft | Phase 1 — `url` trigger fires on matching URL, fire-once-per-session, dedup |
| [custom-event-form-submit-on-url](flows/custom-event-form-submit-on-url.md) | Draft (deferred) | Phase 1 (TBD) — form_submit_on_url trigger fires when form submits on matching URL |
| [event-name-dropdown](flows/event-name-dropdown.md) | Draft | Phase 2 — `event_name` field becomes platform-specific dropdown with Custom escape hatch |
| [centralised-conversion-builder](flows/centralised-conversion-builder.md) | Draft | Phase 3 — builder UI creates linked rows, G-001 enforced inline, conceptual event mapping |
| [conversion-group-management](flows/conversion-group-management.md) | Draft | Phase 3 — group edit/delete/detach/add-platform lifecycle |

_(More flows added as features ship. New flow → add a row here.)_

### Backlog (not yet written)

- pageview-no-consent — Default state before any decision (covered by Scenario 1 of consent-grant; may not need its own flow)
- bot-traffic-excluded — Bot UAs don't fire events (verify behaviour exists first)
- admin-user-excluded — Logged-in admin doesn't fire events (if setting exists; verify)
- consent-popup-styles — Each banner style (centred, top, bottom, corner) renders correctly
- multi-currency — Event payloads use correct currency code when WC currency changes
- gdpr-region-only — If geo-gating exists, banner appears only for EU IPs

### Findings while drafting flows

- `woocommerce_before_checkout_form` hook for InitiateCheckout is **commented out** in `woocomm-hook-handling/hook-handlers-checkout.php:14`. So InitiateCheckout currently does not fire from that hook. Worth confirming whether this is intentional or a regression — if intentional, no test needed; if regression, add a flow.
