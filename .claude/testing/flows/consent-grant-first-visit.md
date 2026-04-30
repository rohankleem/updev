# Flow: Consent grant — first visit

**Status:** Draft
**Last run:** —
**Covers:** Banner appearance on first visit, Accept All button, marketing pixels held until consent, post-consent pixel firing, persistence across reload, server-side mirror in Event Log.

## Setup

- Browser session has no `unipixel_consent_summary` cookie (clear cookies for `updev.local.site` if needed)
- All five platforms enabled in admin with test pixel IDs:
  - Meta, Google, TikTok, Pinterest, Microsoft
- Consent popup enabled with default settings (Accept all + Reject all + Adjust preferences buttons visible)
- Starting URL: `https://updev.local.site/`

---

## Scenario 1: Banner renders, marketing pixels held

**Action:** Navigate to `https://updev.local.site/`. Wait for `DOMContentLoaded`.

**Asserts:**
- Banner element `#unipixel-consent-banner` exists and is visible
- Overlay element `#unipixel-consent-overlay` exists (default `force_choice=true`)
- Buttons present: `#upx-ok` (Accept all), `#upx-reject` (Reject all if `show_reject=true`), `#upx-adjust` (Adjust preferences)
- Cookie `unipixel_consent_summary` does NOT exist
- `typeof window.fbq` is `undefined` OR fbq is the placeholder stub (no script loaded)
- `typeof window.gtag` is `undefined` OR gtag stub only
- `typeof window.ttq` is `undefined`
- `typeof window.pintrk` is `undefined`
- No network requests to: `graph.facebook.com`, `googletagmanager.com`, `analytics.tiktok.com`, `ct.pinterest.com`, `bat.bing.com`, `google-analytics.com`

**Captures:**
- DOM snapshot of `#unipixel-consent-banner` outerHTML → `expected/scenario-1-banner-html.txt`
- Full network request list (URLs only, before consent) → `expected/scenario-1-network-pre-consent.json`

---

## Scenario 2: Click "Accept all"

**Action:** Click `#upx-ok`.

**Asserts:**
- `#unipixel-consent-banner` removed from DOM
- `#unipixel-consent-overlay` removed from DOM
- Cookie `unipixel_consent_summary` set with value (URL-decoded JSON): `{"necessary":true,"functional":true,"performance":true,"marketing":true}`
- Within 2s: `typeof window.fbq === 'function'`
- Within 2s: `typeof window.gtag === 'function'`
- Within 2s: `typeof window.ttq === 'object'` (TikTok exposes ttq as object)
- Within 2s: `typeof window.pintrk === 'function'`
- Network request to `graph.facebook.com/tr` with `ev=PageView` seen
- Network request to `google-analytics.com/g/collect` OR `googletagmanager.com/gtag` with `en=page_view` seen
- Network request to `analytics.tiktok.com/api/v2/pixel` with `event=Pageview` seen
- Network request to `ct.pinterest.com/v3` with `event=pagevisit` seen
- Network request to `bat.bing.com` (UET) seen

**Captures:**
- Meta `/tr` PageView request payload → `expected/scenario-2-meta-pageview.json`
- GA collect request payload → `expected/scenario-2-ga4-pageview.json`
- TikTok pixel request payload → `expected/scenario-2-tiktok-pageview.json`
- Pinterest pixel request payload → `expected/scenario-2-pinterest-pageview.json`
- Microsoft UET request payload → `expected/scenario-2-bing-pageview.json`
- `unipixel_consent_summary` cookie value (URL-decoded) → `expected/scenario-2-consent-cookie.json`

---

## Scenario 3: Persistence across reload

**Action:** Full page reload (`location.reload()`).

**Asserts:**
- `#unipixel-consent-banner` does NOT appear (no element in DOM after `DOMContentLoaded`)
- `#unipixel-consent-overlay` does NOT appear
- Cookie `unipixel_consent_summary` still present with same value
- Pixels load on page (no delay): `window.fbq`, `window.gtag`, `window.ttq`, `window.pintrk` all defined within 2s
- PageView fires for each platform (network requests seen, same endpoints as Scenario 2)

**Captures:**
- Meta PageView payload from second load → diff against `expected/scenario-2-meta-pageview.json`
  - Expected diff: `event_id` differs, `eventID` differs, timestamp differs. Everything else identical.

---

## Scenario 4: Server-side mirror in Event Log

**Action:** Navigate to `https://updev.local.site/wp-admin/admin.php?page=unipixel-event-logs` (assumes WP admin session active).

**Asserts:**
- Page loads (no permission error)
- Event Log table contains rows with timestamp within last 60s
- For each platform that has CAPI enabled, at least one PageView row from this session
- Each browser-side PageView has a matching CAPI row with the same `event_id`

**Captures:**
- Most recent PageView row data per platform → `expected/scenario-4-eventlog-pageview-{platform}.json`

**Known gaps:**
- Verifying Meta Events Manager actually received and matched the CAPI event is a human spot-check. Not part of this flow.

---

## Known gaps for this flow

- Doesn't cover Adjust Preferences flow (granular consent) — separate flow.
- Doesn't cover declined consent — separate flow.
- Doesn't cover revocation after grant — separate flow.
- Bing UET endpoint may need verification — `bat.bing.com` is the typical endpoint but the plugin's actual implementation should be confirmed on first run.
