# Flow: Consent revoke — granted then revoked

**Status:** Draft
**Last run:** —
**Covers:** User who previously accepted changes mind. Marketing pixels must stop firing on next pageview. Cookie reflects revocation.

## Setup

- Browser session has `unipixel_consent_summary` cookie set to `{"necessary":true,"functional":true,"performance":true,"marketing":true}` (full grant)
- All five platforms enabled with test pixel IDs
- Plugin console logging ON
- A "Manage cookies" / consent-revoke link or button is exposed somewhere on the site (footer, account page) — confirm location on first run, update this setup
- Starting URL: `https://updev.local.site/`

---

## Scenario 1: Confirm starting state — pixels active

**Action:** Navigate to homepage.

**Asserts:**
- No banner shown (consent already given)
- `window.fbq`, `window.gtag`, `window.ttq`, `window.pintrk` defined
- PageView fires for each platform (network requests seen)

---

## Scenario 2: Trigger revoke

**Action:** Locate consent management trigger (footer link, settings page, or programmatic — `[TBD: identify on first run]`). Click it. Use the panel to set marketing → off (or use "Reject all" if exposed in revoke UI).

**Asserts:**
- UI confirms preferences saved
- Cookie `unipixel_consent_summary` updated, `marketing` field flipped to `false`
- DOM update or page reload (depends on plugin behaviour — confirm)

**Captures:**
- Pre-revoke cookie value → `expected/scenario-2-pre-revoke-cookie.json`
- Post-revoke cookie value → `expected/scenario-2-post-revoke-cookie.json`

---

## Scenario 3: Next pageview — marketing pixels do NOT fire

**Action:** Navigate to a different page (e.g. `/sample-page/`). Wait `DOMContentLoaded` + 2s.

**Asserts:**
- No requests to `graph.facebook.com`, `analytics.tiktok.com`, `ct.pinterest.com`, `bat.bing.com`
- Behaviour for Google: confirm whether GA stops firing entirely or sends with denied consent state — may differ by config
- `window.fbq`, `window.ttq`, `window.pintrk` either not defined OR defined but no events sent (confirm on first run which path the plugin takes)

**Captures:**
- Network request list post-revoke → `expected/scenario-3-network-post-revoke.json`

---

## Scenario 4: Reload — state persists

**Action:** Full reload.

**Asserts:**
- Cookie still in revoked state
- No marketing requests fire
- No banner re-appears (decision is recorded — `unipixel_consent_summary` exists)

---

## Known gaps

- Exact UI for triggering revoke needs identification on first run — update Setup with the real selector.
- Plugin behaviour for Google after revoke (full suppression vs Consent Mode denied) — confirm.
