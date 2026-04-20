# Feature: Multi-Tier Click ID Persistence

**Status:** Not started
**Bucket:** Event Quality
**Effort:** Days
**Priority:** High — general improvement with real-world impact across all platforms

---

## Summary

Click IDs from ad platforms (`fbclid` for Meta, `gclid` for Google, `ttclid` for TikTok, `msclkid` for Microsoft, `epik` for Pinterest) are currently captured from the landing page URL and stored in a single first-party cookie. At purchase time, UniPixel reads back that cookie and sends the click ID as part of the attribution payload (e.g. `fbc` for Meta CAPI).

This is a **single point of failure**. When the cookie is lost for any reason — browser privacy features, cross-app handoff, redirects, cache oddities — attribution data is missing from the event sent to the platform. The event still fires and is accepted, but the platform cannot link it back to the ad click, and the conversion is not attributed to the campaign.

This feature adds a **multi-tier persistence layer**: when a click ID is captured, it is written to several storage locations so that losing one does not lose attribution. At purchase time, the plugin checks each location in a fallback chain and uses the first available value.

The improvement is genuinely valuable across real-world scenarios — not a workaround for one edge case.

---

## What this solves

### 1. In-app browser → Safari handoff

Extremely common. Customer sees a Meta ad inside the Instagram or Facebook app, taps it, lands in the in-app webview. They browse, then the link opens in Safari (either via the "Open in Safari" button or because the checkout link opens externally). **Cookies do not carry between apps.**

With the current implementation: the `unipixel_fbclid` cookie set in the in-app browser is gone the moment Safari takes over. Attribution is lost.

With multi-tier persistence: if the fbclid was written to a WooCommerce session attached to the cart, or copied to order meta during checkout initiation, the identifier survives the handoff.

### 2. Redirects that strip URL parameters

Some sites redirect the landing URL before UniPixel's `init` hook has a chance to read `$_GET['fbclid']`:

- HTTPS upgrades (when not configured to preserve query strings)
- Trailing slash redirects
- Language/region redirects
- Marketing plugin redirects

Currently, if the redirect drops the query string, the fbclid is lost on that page load. Multi-tier persistence alone can't fix the initial miss, but combined with capturing fbclid into the session on **any** page load where it's seen (not just the first one), resilience improves significantly.

### 3. Orders permanently record their attribution source

This is arguably the most user-visible improvement.

Right now, a WooCommerce merchant looking at an order three months later has no way to see where it came from. The click ID was in a browser cookie that no longer exists. The attribution existed only long enough to be sent to the ad platform.

With this feature, every order stores its captured click IDs in order meta. A merchant can open any order in WooCommerce admin and see exactly which ad click (if any) led to that purchase. This turns UniPixel into an attribution audit tool as well as a tracking tool.

### 4. Debugging and transparency

Merchants who escalate attribution issues currently have no way to verify *what was captured* vs *what was lost*. Stored Event Logs show what was sent, but not what happened between landing and checkout.

With click IDs written to the order, a merchant (or support request) can check:

- Did this order land with an fbclid originally?
- When was it captured?
- Was it captured during checkout or before?
- Which storage tier survived?

This eliminates the "was the ad click even recorded?" guesswork that currently plagues support cases.

### 5. Caching, CDN and race condition edge cases

On sites with aggressive caching, CDN layers, or unusual plugin stacks, cookie writes occasionally fail silently. Having a session write as a secondary path means a failed cookie write doesn't automatically mean lost attribution.

### 6. Consistent behaviour across all supported ad platforms

Meta (`fbclid`), Google (`gclid`), TikTok (`ttclid`), Microsoft (`msclkid`), and Pinterest (`epik`) all currently suffer the same single-cookie-point-of-failure. This feature lifts them all at once with one shared pattern.

---

## What this does NOT solve

**Be honest about the limits:**

### Apple Safari iOS Intelligent Tracking Prevention (ITP) 7-day cliff

If a customer clicks an ad on Safari iOS, then returns 7+ days later to complete the purchase, Safari has by that point evicted every cookie — including the WooCommerce session cookie that would be needed to reconnect the server-side session data to the returning visitor. Without that cookie, the returning customer is indistinguishable from a first-time visitor. No server-side storage can help, because there's no identifier left to look it up by.

This is an Apple-enforced limitation and affects every tracking plugin equally. Attribution for that specific cross-day, cross-session iOS scenario cannot be recovered by any means at the WordPress plugin layer.

### Ad clicks that never reach the site

If the landing URL never contained the fbclid — the customer saw the ad but typed the URL manually, searched for the brand on Google, used a VPN that stripped query strings, or the ad used a tracking template that never resolved — there's nothing to capture, store, or restore.

### Browser pixel blocked entirely

Advanced Matching values like `_fbp` are set by Meta's own `fbq()` pixel in the browser. If content blockers, privacy tools, or consent denial prevent the pixel from firing, `_fbp` is never created and cannot be recreated server-side. This feature does not address that.

---

## Design

### Storage tiers (in order of durability)

| Tier | Persistence | Scope | Notes |
|---|---|---|---|
| 1. First-party cookie (`unipixel_fbclid`) | 7–90 days (browser-dependent) | Per-browser | Current mechanism. Subject to ITP. |
| 2. WooCommerce session | Duration of WC session (~2 days default, tied to WC session cookie) | Per-browser | Follows existing pattern in `hook-handlers-checkout.php` |
| 3. WordPress transient (keyed by user identifier) | 30 days | Per-IP+UA (or user ID if logged in) | Follows existing pattern in `hook-handlers-addtocart.php`; uses existing `unipixel_get_user_identifier_for_transient()` |
| 4. User meta (logged-in users) | Permanent | Per-user | Follows `add_user_meta` / `get_user_meta` pattern |
| 5. Order meta (once checkout starts or completes) | Permanent | Per-order | Attached to the order itself; permanent record |

### Capture flow

On every page load via `init` hook (existing `unipixel_capture_fbclid` function, extended):

1. If `$_GET['fbclid']` is present:
   - Continue writing the first-party cookie (current behaviour preserved)
   - Additionally write to WC session if `WC()->session` is available
   - Additionally write to transient keyed by `unipixel_get_user_identifier_for_transient()`
   - If user is logged in, write to user meta
2. Same pattern for `gclid`, `ttclid`, `msclkid`, `epik` (some of these are already being partially handled — verify and extend)

### Checkout initiation flow

On `woocommerce_checkout_create_order` or equivalent hook, just before the order is persisted:

1. For each click ID, look up the best-available value from tiers 1–4 (cookie → WC session → transient → user meta)
2. Write the value into order meta as `_unipixel_fbclid`, `_unipixel_gclid`, etc.
3. Also write the captured timestamp so merchants can see *when* the click happened

### Purchase event preparation flow

Modify `unipixel_get_fbc_value()` and equivalent functions to check the fallback chain:

1. Order meta (most reliable once checkout has happened)
2. WC session
3. Transient
4. User meta
5. Cookies (current mechanism, last resort)

Return the first non-empty value found. The `fbc` synthesis logic (`fb.<domainIndex>.<timestamp>.<fbclid>`) stays the same.

### Same pattern applied to `_fbp`

`_fbp` is set by Meta's browser pixel, not by UniPixel — but we can capture it into the same multi-tier storage on any page load where `$_COOKIE['_fbp']` is present. This protects against the same cookie-loss scenarios.

---

## UI implications

### Order admin

In WooCommerce → Orders → single order view, add a section (or meta box) showing captured click IDs:

- Meta (Facebook) click: `fbclid=ABC123...` captured 2026-04-10 14:32
- Google click: `gclid=XYZ789...` captured 2026-04-10 14:32
- (etc.)

If none were captured: "No ad click identifiers captured for this order. The customer may have arrived organically, or attribution data was lost before checkout."

### Stored Event Logs

Already shows what was sent. No change needed — but the attribution payload will now be populated correctly in more cases.

---

## Implementation files affected

| File | Change |
|---|---|
| `functions/hooks.php` | Extend `unipixel_capture_fbclid()` and equivalents for other platforms to write to WC session, transient, and user meta alongside the cookie |
| `functions/unipixel-functions.php` | Extend `unipixel_get_fbc_value()` and equivalents with fallback chain reading from all tiers |
| `woocomm-hook-handling/hook-handlers-purchase.php` (or checkout hook) | On checkout initiation, snapshot current click IDs into order meta |
| `woocomm-hook-handling/prepare-common-to-platform-purchase.php` | Use order meta as primary source when preparing Meta/Google/TikTok/Microsoft/Pinterest payloads for WooCommerce events |
| `woocomm-hook-handling/get-common-woo-data-purchase.php` | Read `_fbp` from multi-tier storage, not just `$_COOKIE['_fbp']` |
| `admin/` (new file or hook) | Add order admin meta box showing captured click IDs per order |

---

## Verification plan

1. **Same-session capture and send:** land with `?fbclid=TEST1`, purchase immediately. Confirm payload has `fbc=fb.1.<ts>.TEST1`.
2. **Cookie deleted mid-session:** land with `?fbclid=TEST2`, manually delete `unipixel_fbclid` cookie, purchase. Confirm payload still has `fbc` from WC session fallback.
3. **All cookies deleted but same WC session:** land, delete cookies except WC session cookie, purchase. Confirm session fallback works.
4. **Logged-in user across sessions:** land logged in with `?fbclid=TEST3`, close browser, log in again on new browser, purchase. Confirm user meta fallback works.
5. **Order meta persistence:** purchase with `?fbclid=TEST4`, check WooCommerce order admin shows fbclid captured. Check order is queryable for its click ID three months later.
6. **Multi-platform:** repeat with `gclid`, `ttclid`, `msclkid`, `epik`.
7. **No click ID:** land without any click ID, purchase. Confirm payload is clean and order admin shows "no attribution data captured."
8. **Safari ITP cliff (expected to fail):** land with fbclid, wait 8+ days on Safari iOS, purchase. Confirm attribution is still lost — we're not pretending to fix this.

---

## Marketing positioning

This feature directly aligns with UniPixel's Sales Pillar 1: "Your ads are wasting money you can't see." It makes more of the real conversions actually reach the ad platforms with the attribution data required for them to be credited to campaigns.

It also enables a genuine, demonstrable visible-in-admin feature: **"See exactly which ad click led to each order, directly in WooCommerce."** That's a marketing-friendly, customer-facing benefit that competitors don't necessarily expose cleanly.

Changelog phrasing (customer-facing, outcome-focused, per the tone rules in `marketing-knowledge/positioning.md`):

> Improvement: More of your ad-driven conversions now reach your ad platforms with proper attribution — even when browser cookies are disturbed during checkout or between apps. Each WooCommerce order now also records which ad click (if any) led to the purchase, directly in the order admin.

---

## Scope decision deferred

Whether to ship this Meta-only first or across all click IDs (Meta, Google, TikTok, Microsoft, Pinterest) in the same release is a later decision. Recommended: implement the shared persistence and fallback pattern once, apply to all platforms in one release, since the code changes are largely shared infrastructure.
