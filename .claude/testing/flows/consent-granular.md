# Flow: Consent granular — adjust preferences

**Status:** Draft
**Last run:** —
**Covers:** Adjust Preferences panel, individual category toggles (functional / performance / marketing), only enabled categories cause pixels to fire.

## Setup

- Browser session has no `unipixel_consent_summary` cookie
- All five platforms enabled
- Plugin console logging ON
- Starting URL: `https://updev.local.site/`

---

## Scenario 1: Open Adjust Preferences panel

**Action:** Navigate to homepage. Click `#upx-adjust`.

**Asserts:**
- `#unipixel-consent-banner` removed (or hidden)
- `#unipixel-consent-panel` visible
- Category switches present: necessary (locked on), functional, performance, marketing
- All toggleable categories default checked = true (per plugin code default)

**Captures:**
- Panel DOM snapshot → `expected/scenario-1-panel-html.txt`

---

## Scenario 2: Enable analytics only (performance + necessary)

**Action:** Uncheck `marketing` and `functional`. Confirm `performance` and `necessary` are checked. Click Save (selector `[TBD: identify on first run]`).

**Asserts:**
- Panel closes
- Cookie `unipixel_consent_summary` set to: `{"necessary":true,"functional":false,"performance":true,"marketing":false}`
- Within 2s:
  - Google (analytics) fires: GA4 collect request seen
  - Meta does NOT fire: no `graph.facebook.com/tr`
  - TikTok does NOT fire: no `analytics.tiktok.com`
  - Pinterest does NOT fire
  - Microsoft does NOT fire (UET is marketing? confirm category mapping on first run)

**Captures:**
- Cookie value → `expected/scenario-2-cookie.json`
- Network request list → `expected/scenario-2-network.json`

---

## Scenario 3: Enable marketing only

**Action:** Reset (clear cookie, refresh). Open panel. Check `marketing` only, uncheck others except necessary. Save.

**Asserts:**
- Cookie reflects marketing-only state
- Marketing platforms fire (Meta, TikTok, Pinterest, Microsoft)
- Google (if categorised as analytics) does NOT fire

**Captures:**
- Cookie value → `expected/scenario-3-cookie.json`
- Network request list → `expected/scenario-3-network.json`

---

## Known gaps

- The exact category mapping per platform (Google = performance? Microsoft UET = marketing?) needs confirmation on first run from `functions/consent.php` or admin UI. Update asserts after verification.
- The Save button selector in the panel is `[TBD]` — capture on first run.
