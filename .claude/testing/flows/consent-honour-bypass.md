# Flow: enableConsentHonour bypass

**Status:** Draft
**Last run:** —
**Covers:** The `enableConsentHonour` toggle on the Consent Settings page. When off, the plugin fires marketing pixels regardless of consent state. This is the bypass mode (rare, intentional). Verifies that flipping the toggle off makes events fire on first visit without consent, and that flipping it on (default) restores the consent gate.

Complements `consent-grant-first-visit` and `consent-decline-first-visit` which both assume `enableConsentHonour = 1`.

This is a high-stakes toggle with real-world implications. A site running with `enableConsentHonour = 0` in EU/UK/AU jurisdictions is non-compliant, but the plugin permits it because some site owners run in non-regulated jurisdictions or rely on a separate consent system upstream of UniPixel. The flow tests the mechanic, not the legality.

## Setup

- WordPress site with the plugin active and at least one platform fully configured (Meta is convenient)
- All platform-level enables on: `platform_enabled = 1, serverside_global_enabled = 1` for Meta
- Meta PageView and ViewContent rows: `send_client = 1, send_server = 1, send_server_log_response = 1`
- Baseline: per `baseline-state.md`. Specifically, `enableConsentHonour` is on by default per `baseline-state.md` (or per fresh-install defaults until that file is locked down).

## Scenario 1: `enableConsentHonour = 1` (default) — no events fire before consent

This is the consent-gated path that `consent-grant-first-visit` Scenario 1 already covers. Re-stated here for the contrast with Scenario 2.

**State delta from baseline:**
- Consent Settings: `enableConsentHonour = 1` (default)
- Browser: clear all `unipixel_consent_*` cookies and localStorage (fresh first visit)

**Action:** Visit the site homepage as a fresh visitor (no prior consent decision).

**Asserts:**
- Consent banner is visible
- No network requests to `graph.facebook.com/*` or `connect.facebook.net/.../tr` for the run window
- No row in `wp_unipixel_event_log` with `platform_name = 'Meta', method IN ('client', 'server')` for the run window
- `window.fbq` may or may not be defined (initialisation can happen without firing events; record whichever the plugin does)

**Captures:**
- Network log filtered for Meta endpoints (should be empty) → `expected/scenario-1-meta-network.json`

---

## Scenario 2: `enableConsentHonour = 0` — events fire on first visit, no consent decision

The bypass. Toggle off, no consent given, events should fire anyway.

**State delta from baseline:**
- Consent Settings: `enableConsentHonour = 0`
- Browser: clear all `unipixel_consent_*` cookies and localStorage (fresh first visit, same as scenario 1)

**Action:** Visit the site homepage as a fresh visitor.

**Asserts:**
- Consent banner: capture whether it shows or not (the popup display is governed by `consent_ui` and may be independent of `enableConsentHonour`; document the actual behaviour)
- Meta PageView fires browser-side (network request to Meta, `window.fbq('track', 'PageView')` called)
- Meta PageView fires server-side (CAPI request from WordPress server to `graph.facebook.com/v.../events`)
- A row exists in `wp_unipixel_event_log` with `platform_name = 'Meta', event_name = 'PageView'` (likely two rows: one client, one server)
- `response_message` populated for the server-side row (since `send_server_log_response = 1`)

**Captures:**
- Network log filtered for Meta endpoints → `expected/scenario-2-meta-network.json`
- The PageView rows from `wp_unipixel_event_log` → `expected/scenario-2-pageview-rows.json`

---

## Scenario 3: Toggle change at runtime takes effect on next pageview

Tests that flipping the toggle has effect immediately on the next page load (no caching weirdness, no need to clear visitor state).

**State delta from baseline:**
- Start with `enableConsentHonour = 1`
- Browser: clear consent state

**Action:**
1. Visit the homepage. Confirm no events fire (per Scenario 1).
2. Flip `enableConsentHonour = 0` via WP CLI or direct DB edit.
3. Reload the homepage.
4. Confirm events fire (per Scenario 2).

**Asserts:**
- Step 1: no Meta event_log rows
- Step 4: Meta PageView row appears in event_log within seconds of the reload
- The contract: a runtime toggle change does not require a full plugin reactivation, cache clear, or visitor cookie reset

**Captures:**
- Run log notes the timing — does the change take effect immediately, after a server-side cache TTL, or never until something explicit invalidates it?

---

## Scenario 4: Toggle on + revoked consent — events stop

Cross-check with `consent-revoke`: granting consent fires events, revoking suppresses them again. This scenario specifically tests that with `enableConsentHonour = 1`, the consent state is the gate and a revocation works.

**State delta from baseline:**
- Consent Settings: `enableConsentHonour = 1`
- Visitor has previously granted consent (apply via `unipixel_consent_summary` cookie or matching localStorage state per the plugin's actual consent storage)

**Action:**
1. Visit the homepage. Confirm Meta PageView fires.
2. Open the consent banner, click "Reject all" (or revoke via Adjust Preferences).
3. Reload the homepage.
4. Confirm Meta PageView does not fire on the reload.

**Asserts:**
- Step 1: PageView fires
- Step 4: no PageView rows in event_log for the post-revoke run window
- The visitor's consent state is updated in the cookie/localStorage to reflect the revocation

**Captures:**
- Pre-revoke and post-revoke event_log windows for diff

---

## Notes for the runner

- This flow is the cleanest way to expose any code path that fires events before checking consent. If Scenario 1 fails (events fire when they shouldn't), there is a hook handler somewhere that doesn't consult the consent gate. Highest priority to fix.
- `enableConsentHonour = 0` is a legitimate setting for non-regulated jurisdictions. Don't assume it's always wrong; the plugin permits it deliberately.
- The third-party CMP path (`consent_ui = 'thirdparty'`) is a separate concern and has its own flow `consent-granular`. This flow tests the toggle in isolation; combine variants if exercising the third-party path with the toggle off (rare combination).
- The bypass mode interacting with consent banner display: the banner may or may not show when `enableConsentHonour = 0` — document the actual behaviour on first run rather than asserting upfront.
