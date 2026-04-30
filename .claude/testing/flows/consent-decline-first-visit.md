# Flow: Consent decline — first visit

**Status:** Draft
**Last run:** —
**Covers:** Reject All button suppresses all marketing pixels, cookie records decline, persistence across reload, Event Log shows no marketing CAPI events.

## Setup

- Browser session has no `unipixel_consent_summary` cookie
- All five platforms enabled in admin with test pixel IDs
- Consent popup enabled with `show_reject = true`
- Plugin console logging ON (so we can see suppressed-by-consent events in console)
- Starting URL: `https://updev.local.site/`

---

## Scenario 1: Banner shows with Reject button

**Action:** Navigate to `https://updev.local.site/`. Wait for `DOMContentLoaded`.

**Asserts:**
- `#unipixel-consent-banner` visible
- `#upx-reject` button visible (text: "Reject all" or configured equivalent)
- `#upx-ok`, `#upx-adjust` also visible

**Captures:**
- _(none — covered by consent-grant scenario 1)_

---

## Scenario 2: Click "Reject all"

**Action:** Click `#upx-reject`.

**Asserts:**
- Banner removed from DOM, overlay removed
- Cookie `unipixel_consent_summary` set with value (URL-decoded JSON): `{"necessary":true,"functional":false,"performance":false,"marketing":false}`
- After 2s wait: still no requests to `graph.facebook.com`, `analytics.tiktok.com`, `ct.pinterest.com`, `bat.bing.com`
- After 2s wait: GA4 either does not load OR loads with `consent_mode` denied (depends on plugin config — confirm on first run)
- Browser console shows messages indicating events suppressed by consent (when console logging is on)

**Captures:**
- `unipixel_consent_summary` cookie value → `expected/scenario-2-decline-cookie.json`
- Browser console output during 2s post-decline → `expected/scenario-2-console-output.txt`
- Network request list (URLs only) for 2s post-decline → `expected/scenario-2-network-post-decline.json`

---

## Scenario 3: Persistence across reload

**Action:** Full page reload.

**Asserts:**
- Banner does NOT reappear
- Cookie still present with same declined-state value
- No marketing platform requests fire on reload
- No `window.fbq`, `window.ttq`, `window.pintrk` defined (or stubs only)

---

## Scenario 4: Event Log confirms no CAPI marketing events

**Action:** Navigate to `wp-admin/admin.php?page=unipixel-event-logs` (assumes admin session).

**Asserts:**
- No PageView CAPI rows for Meta/TikTok/Pinterest/Microsoft from this session timestamp
- (Google may still log a `consent_denied` PageView if plugin sends Consent Mode signals — confirm behaviour)

---

## Known gaps

- Plugin's exact behaviour with declined consent for Google (Consent Mode v2 vs full suppression) needs confirmation on first run.
- Doesn't cover what happens when user later changes mind — see `consent-revoke.md`.
