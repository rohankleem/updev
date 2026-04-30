# Flow: Click ID capture from URL

**Status:** Draft
**Last run:** —
**Covers:** URL parameters (`fbclid`, `gclid`, `ttclid`, `msclkid`) captured into plugin cookies on first visit and persisted across pages and sessions.

## Setup

- Browser session has none of these cookies: `unipixel_fbclid`, `unipixel_gclid`, `unipixel_ttclid`, `unipixel_msclkid`
- Consent already granted (full) — click ID capture should not be blocked by consent for "necessary" but confirm on first run
- Starting state: clean session, then navigate to URL with all four IDs

---

## Scenario 1: First visit with all four click IDs

**Action:** Navigate to:
`https://updev.local.site/?fbclid=test_fbclid_123&gclid=test_gclid_456&ttclid=test_ttclid_789&msclkid=test_msclkid_abc`

**Asserts:**
- Cookie `unipixel_fbclid` exists, value contains `test_fbclid_123`
- Cookie `unipixel_gclid` exists, value contains `test_gclid_456`
- Cookie `unipixel_ttclid` exists, value contains `test_ttclid_789`
- Cookie `unipixel_msclkid` exists, value contains `test_msclkid_abc`
- Meta `_fbc` cookie either set directly OR derivable via plugin (`fb.{domainIndex}.{ts}.test_fbclid_123` format — see `unipixel-functions.php:513`)

**Captures:**
- All four cookie values → `expected/scenario-1-clickid-cookies.json`

---

## Scenario 2: Persistence across page navigation

**Action:** Click any internal link (e.g. navigate to `/sample-page/`).

**Asserts:**
- All four `unipixel_*clid` cookies still present, same values
- New URL has no `?...clid=` params, but cookies survive

---

## Scenario 3: Click IDs included in subsequent CAPI events

**Action:** Trigger any event that fires CAPI — simplest is a PageView on the same session. Open Event Test Console (`wp-admin/admin.php?page=unipixel_console_logger`) OR check Stored Event Logs.

**Asserts:**
- Meta CAPI PageView payload includes `user_data.fbc` derived from the captured fbclid (or raw fbclid in custom data)
- Google CAPI / Measurement Protocol payload includes `gclid`
- TikTok CAPI payload includes `ttclid`
- Microsoft / Bing payload includes `msclkid`

**Captures:**
- Meta PageView payload (CAPI) showing fbc → `expected/scenario-3-meta-capi-with-fbc.json`
- Google MP payload showing gclid → `expected/scenario-3-google-mp-with-gclid.json`
- TikTok CAPI payload → `expected/scenario-3-tiktok-capi-with-ttclid.json`
- Microsoft payload → `expected/scenario-3-microsoft-with-msclkid.json`

---

## Scenario 4: Re-visit with new click ID — cookie updates

**Action:** Same browser session (cookies persist). Navigate to:
`https://updev.local.site/?fbclid=test_fbclid_NEW`

**Asserts:**
- `unipixel_fbclid` cookie value updated to `test_fbclid_NEW` (overwrites old)
- Other three click ID cookies unchanged (gclid, ttclid, msclkid still hold scenario 1 values)

---

## Known gaps

- Cookie expiry / lifespan (90 days typical for fbc) — verify on first run.
- Behaviour when consent is declined — does click ID capture still occur? It probably should (necessary for attribution) but plugin's actual behaviour needs confirmation. If declined-but-still-captured, no CAPI events use them anyway.
- `_fbc` cookie format vs `unipixel_fbclid` — confirm whether plugin sets `_fbc` directly or only `unipixel_fbclid`.
