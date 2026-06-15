# Microsoft event-name mismatch

**Status:** Active (parked overnight 2026-05-13). Not started.
**Origin:** Live-site diagnostic 2026-05-13. Real revenue impact — Microsoft Ads not recording any Purchase conversions on steelchief.com.au despite UET events firing correctly.

---

## The issue in one line

UniPixel sends `purchase` (lowercase) to Microsoft UET, but the live site's `Purchase 2` conversion goal expects `EventAction = "Purchase"` (capital P, EqualsTo). Case mismatch means zero conversions record.

## What we verified (evidence, not speculation)

1. **Stored Event Logs (live site)** prove `uetq.push('event', 'purchase', {…})` IS firing many times per day, going back months. Plus `add_to_cart`, `begin_checkout`, `view_item`, all lowercase snake_case.
2. **Microsoft Ads → Conversion goals → Purchase 2 → Copilot diagnostic** returned the exact rule: `Conversion Rule: EventAction = "Purchase" (EqualsTo)`. Goal name `Purchase 2` is just a label / description; the actual matching string is `Purchase`.
3. **`All conv.` column proves the matching is the gate, not delivery.** ClickOpenConfigurator goal shows `All conv. = 3` — Microsoft IS receiving and matching events. Purchase 2 shows `All conv. = -` (zero) — Microsoft is NOT matching anything against this goal. Combined with the logs showing purchase events firing, the only explanation is the rule isn't matching.
4. **The bespoke (PascalCase) goals work.** ClickOpenConfigurator and ConfiguratorShownPrice both record conversions because UniPixel's Site Events config has them as Bespoke values matching the goal name's capitalisation.

## Microsoft's docs are genuinely useless on this

- Their own "How to track custom events with UET" help link returns 404 ([56684](https://help.ads.microsoft.com/apex/index/3/en/56684))
- Different examples in different Microsoft docs use: `'purchase'` lowercase, `'PRODUCT_PURCHASE'` uppercase, `'AutoEvent_purchase'` mixed, empty string + `ecomm_pagetype: 'purchase'`
- A Microsoft support specialist on their Q&A site explicitly **refused to answer publicly** when asked which event name to use, deflecting to private DMs ([learn.microsoft.com Q&A 2290218](https://learn.microsoft.com/en-us/answers/questions/2290218/what-are-the-correct-uet-purchase-events-to-send))

**There is no canonical Microsoft UET event-name standard.** The "correct" event Action name is whatever string the user's goal rule was set up to match. Period.

The de facto convention: Microsoft Ads' goal-creation wizard, when you pick a category like "Purchase" from the dropdown, auto-fills the rule as `EventAction = "Purchase"` (PascalCase). Most users go through this path, so PascalCase is the practical norm.

## Three solution options (decision needed before code changes)

### A. Hardcode PascalCase Microsoft event names

Change UniPixel's WooCommerce auto-events to send PascalCase:
- `add_to_cart` → `AddToCart`
- `begin_checkout` → `InitiateCheckout`
- `purchase` → `Purchase`
- `view_item` → `ViewContent`
- Plus the Site Events Standard dropdown: `lead` → `Lead`, etc.

Plus a one-time DB migration for existing installs so `wp_unipixel_woocomm_event_settings.event_platform_ref` rewrites in place without losing user toggle settings. Plus a version bump (so the migration auto-fires via `unipixel_check_version()` on plugins_loaded).

**Pro:** simple, matches Microsoft UI's default convention, works for the common case.
**Con:** wrong for users whose goal rules use other conventions (lowercase, `AutoEvent_purchase`, `PRODUCT_PURCHASE`, etc.). One-size-fits-all in a domain where Microsoft has no canon.

**Files touched:** 10 — see implementation notes below.

### B. Translation layer at send-time

Keep DB values as the canonical internal key (lowercase snake_case, GA4-aligned for consistency across platforms). Add a function like `unipixel_microsoft_wire_name($internal)` that translates at the moment of send.

**Pro:** decouples internal identifier from wire format. If Microsoft changes its convention, only the map changes.
**Con:** another translation layer to maintain. Still picks one Microsoft convention (and is still wrong for users whose goals use other conventions). The admin UI "Event Name Sent to Microsoft" column would still show snake_case even though we send PascalCase, which is misleading.

### C. Make `event_platform_ref` editable per row for WooCommerce events (Rohan's preferred direction)

Currently:
- **Site Events table** — users CAN type whatever they want into the Platform Event Ref field (Bespoke), or pick from a Standard dropdown.
- **WooCommerce events table** — `event_platform_ref` is read-only display. Hardcoded by UniPixel.

The asymmetry is the real bug. Make the WooCommerce table's `event_platform_ref` editable too, so each user can match it to their goal's actual rule string. No code-side assumption about Microsoft's "standard".

**Pro:** correct for every user regardless of how their goals were set up. Mirrors the Site Events table pattern users already understand. Doesn't require UniPixel to pick a Microsoft convention.
**Con:** more UI work. Need to handle DB lookup when the user changes the value (the lookup currently keys on `event_platform_ref` — changing it would lose toggle settings unless we add a stable internal key column). Existing installs need defaults that work out of the box (so PascalCase defaults are still right, but the user can change them).

This is closest to right. The Site Events table already has this affordance — we'd just extend it to WooCommerce events.

---

## What to do (next session)

1. **Decide between B and C** (A is the fastest but least correct). C is the better long-term fix; B is a halfway house.
2. If choosing **C**:
   - Add a stable internal key column to `wp_unipixel_woocomm_event_settings` (e.g., `event_internal_ref` = `add_to_cart`, `purchase` etc.) so DB lookup stays stable while `event_platform_ref` becomes user-editable.
   - Migrate existing rows: copy current `event_platform_ref` → new `event_internal_ref` column. Initialize `event_platform_ref` to PascalCase defaults (the de facto Microsoft norm) but mark column editable.
   - Update hook handlers to look up by `event_internal_ref`, send `event_platform_ref` (current column repurposed as wire format).
   - Update `admin/page-microsoft-events.php` WooCommerce table to render `event_platform_ref` as an editable input.
   - Update save handler to persist edits.
3. **Bump version** (2.6.7 → 2.7.0 likely) when shipping. Auto-migration runs via `unipixel_check_version()` on plugins_loaded.
4. **On the live site after release:** verify Microsoft Ads' Purchase 2 goal starts recording.

## Sibling issues from the same diagnostic (also unresolved)

Found during the same 2026-05-13 investigation, not in scope for this initiative but worth tracking:

### Server-side Microsoft rows missing from logs
The Stored Event Logs show only `client` method rows for Microsoft, no `server` method rows — despite per-event Send Server-side toggles being ON. Most likely cause: the platform-level `serverside_global_enabled` toggle is OFF on the live site's Microsoft platform settings. Code at `hook-handlers-purchase.php:268` gates server-side firing on that flag. **Action:** check Microsoft Setup → Tag Setup → "Enable Server-Side Tracking" master toggle on live.

### Three broken Site Event triggers
Configured but never firing:
- **`ConfiguratorSubmittedYes`** — *On Page URL Match* `*getprice-yes*`. The actual post-submit URL on the live site probably doesn't contain `getprice-yes`. Need to inspect the configurator flow.
- **`lead` (`#thankyousuccess`)** — *On Element Shown*. Element with ID `thankyousuccess` may not actually appear in the DOM on lead success — needs DOM inspection.
- **`lead` (`#bookCallFormSubmitted`)** — Trigger type is *On Page URL Match* but the value `#bookCallFormSubmitted` is a CSS-selector shape, not a URL pattern. Misconfigured row; trigger type or value needs fixing.

These three are independent of the event-name-case issue. Even with names fixed, the triggers wouldn't fire because they never match anything.

---

## Implementation notes (Option A — if we go that way despite being suboptimal)

10 files would need editing. Captured here so the work isn't re-derived:

| File | Change |
|---|---|
| `woocomm-hook-handling/hook-handlers-purchase.php:44` | `$eventNameMicrosoft = "purchase"` → `"Purchase"` |
| `woocomm-hook-handling/hook-handlers-addtocart.php:70` | `"add_to_cart"` → `"AddToCart"` |
| `woocomm-hook-handling/hook-handlers-checkout.php:81` | `"begin_checkout"` → `"InitiateCheckout"` |
| `woocomm-hook-handling/hook-handlers-viewcontent.php:48` | `"view_item"` → `"ViewContent"` |
| `woocomm-hook-handling/client-side-send-purchase.php:246` | `uetq.push('event', 'purchase', ...)` → `'Purchase'` |
| `woocomm-hook-handling/client-side-send-addtocart.php:166 + 673` | `'add_to_cart'` → `'AddToCart'` (2 occurrences) |
| `woocomm-hook-handling/client-side-send-checkout.php:245` | `'begin_checkout'` → `'InitiateCheckout'` |
| `woocomm-hook-handling/client-side-send-viewcontent.php:220` | `'view_item'` → `'ViewContent'` |
| `admin/js/ajax-event-settings.js:32` | Microsoft Standard dropdown: lowercase snake_case → PascalCase list |
| `config/schema.php` | Update 4 Microsoft seed rows (event_platform_ref + event_description). Add new `unipixel_migrate_microsoft_event_case()` function. Call it from `unipixel_update_schema()` BEFORE `unipixel_insert_default_woo_events()` runs. |

Lookup mechanism context: `unipixel_woo_event_get_settings($platform_id, $event_platform_ref)` at `woocomm-hook-handling/helpers.php:45` keys on `event_platform_ref`. So changing the hardcoded send-string requires either migrating the DB rows OR breaking the per-event settings lookup. Migration approach preserves user toggle settings.

Activation flow that triggers the migration: `register_activation_hook` on plugin file → `unipixel_activate` → `unipixel_update_schema`. Also runs on every page load via `plugins_loaded` → `unipixel_check_version` if `UNIPIXEL_VERSION` constant differs from stored option. So version bump is what fires the migration on live.

## What we tried in this session

Implemented Option A end-to-end (all 10 files edited, migration written, PHP lint clean). Then reverted on Rohan's instruction once we landed on the conclusion that editable `event_platform_ref` (Option C) is the better path. Diff history preserved in this session's transcript if reference needed.
