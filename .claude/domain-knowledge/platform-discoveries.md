# Platform Discoveries — Event Quality Investigation

Cross-session audit findings as platform reports are assessed and code is audited. When a platform diagnostic is investigated, the outcome lands here (fixed, dismissed as platform overreach, noted for later, etc.).

---

## TikTok

### TT-001: Event ID mismatch (57%) — Mostly Environmental

**Source:** TikTok Umbrella Diagnostics — "Event ID mismatch between server and browser events"
**Severity reported:** Medium | **Events affected:** 57.02%

**Code audit result:** The event_id flow is correct in both patterns:
- **Server-first (WooCommerce):** event_id generated once in PHP (`purchase_<microtime>`), passed to both TikTok API and client pixel via `wp_localize_script`. Same value reaches `ttq.track()`.
- **Client-first (custom/PageView):** event_id generated once in JS (`event_<timestamp>`), passed to both `ttq.track()` and AJAX → server API. Same value used.

**Root cause assessment:** Not a code logic bug. The 57% is likely **environmental** — one side fires but the other doesn't:
- Ad blockers prevent TikTok SDK (`events.js`) from loading → `ttq.track()` never executes → server has event_id, browser doesn't
- User leaves thank-you page before inline JS runs
- AJAX failures on client-first events → browser fires, server never receives
- TikTok counts unpaired event_ids as "mismatches"

**Action:** No event_id code change needed. Monitor after other fixes land to see if percentage shifts.

**Related bug found:** See TT-002.

---

### TT-003: Reserved event names auto-map to Standard events — TERMINOLOGY QUIRK

**Source:** TikTok Events API docs (Custom Events / Reserved Events)
**Surface impact:** Bespoke event flow (centralised Event Manager + per-platform Custom Events table)

**The quirk:** TikTok defines a set of "Reserved Event Names" that the platform automatically maps to its existing Standard Events. If a user creates a Bespoke event with a Reserved name, TikTok will NOT report it under that bespoke name — it gets silently rolled into the matched Standard event. The user sees no custom-named data flowing.

**Cross-reference:** `event-terminology.md` § Platform-specific quirks. The per-platform inline hint for TikTok carries "Avoid Reserved names" as the warning surface.

**Action when surfacing in UI:** When a user enters a bespoke Platform Event Reference for TikTok, validate against the reserved list and warn inline. Don't block (TikTok still accepts the call); just inform.

---

### TT-002: Parameter ordering bug in Purchase & Checkout server calls — FIXED

**Found during:** TT-001 investigation
**Files affected:**
- `woocomm-hook-handling/hook-handlers-purchase.php` (line ~163)
- `woocomm-hook-handling/hook-handlers-checkout.php` (line ~217)

**The bug:** Function `unipixel_send_server_event_tiktok()` expects:
```
arg 6: $pageUrl (string)
arg 7: $sendServerLogResponse (bool)
arg 8: $consentAlreadyChecked (bool)
```
Purchase and Checkout were passing:
```
arg 6: !empty($wooEventSettingsTikTok->send_server_log_response)  ← boolean, should be URL
arg 7: $consentAlreadyChecked                                      ← shifted
arg 8: (missing)                                                   ← defaults to false
```

**Impact:** `$pageUrl` received `true/false` → TikTok API got a boolean instead of a URL in `context.page.url`. Consent check was also bypassed (always defaulted to false).

**Not affected:** AddToCart and ViewContent — they pass `$dataToSendTikTok['pageUrl']` correctly.

**Fix:** Added `$pageUrl` argument in correct position. Session: 2026-03-01.

---

## Meta

### META-001: "Send valid currency information" for Lead & ConfiguratorShownPrice — DISMISSED

**Source:** Meta Pixel diagnostics (3 days old, may already be stale)
**Events affected:** 100% of Lead, 100% of ConfiguratorShownPrice

**What Meta claims:** These events aren't sending valid currency info. Suggests 5% ROAS improvement.

**Assessment:** These are client-first custom events — user picks an element + trigger + event name. No monetary value is involved. The server-side send exists purely for deduplication counting. Neither client (`event_params = {}`) nor server (`$sanitized_custom_data = []`) sends any custom_data at all — no rogue blank currency field, just correctly empty payloads.

Meta applies its e-commerce data checklist to all standard event names (like "Lead") regardless of context. "ConfiguratorShownPrice" is a custom event name that happens to contain "Price" — Meta flags it by pattern.

**Verdict:** Platform overreach, not a code problem. No action needed. The 5% ROAS claim is Meta's canned upsell messaging.

---

### META-002: Purchase EMQ 7.4/10 — fbc "not sent" + external_id + Facebook Login ID — DISMISSED (fbc), NOTED (external_id)

**Source:** Meta Events Manager — Purchase event shared parameters report

**What's already at 100%:** Email (hashed), IP, User Agent, Phone (hashed), fbp — all green.

**fbc (Click ID) — DISMISSED.** Meta says "server is not sending fbc." Code audit confirms fbc IS sent when available:
- All 4 WooCommerce prepare functions call `unipixel_get_fbc_value()` and include fbc when `strlen > 5`
- Dual capture: JS (`pixel-meta.js`) and PHP (`hooks.php`) both capture fbclid → `unipixel_fbclid` cookie (90 days)
- Fallback: if `_fbc` cookie missing but `unipixel_fbclid` exists, constructs valid `fb.<domainIndex>.<timestamp>.<fbclid>` string
- Client-first AJAX handler (`meta-ajax-listener-send-server.php`) also includes fbc
- Low coverage is because most users don't arrive via Facebook ad clicks → no fbclid → legitimately empty. Plugin already does what Meta's "parameter builder" does.

**External ID — ASSESSED & DEPRIORITISED.** Meta claims 15.52% match improvement from `external_id`. After full analysis (2026-03-14), this is a platform upsell metric, not a genuine quality gap for WooCommerce stores.

**What external_id does:** Gives Meta YOUR persistent identifier for a user, so they can link that user's activity across advertisers in their network. It's Meta building their identity graph using your data.

**Why it doesn't help typical WooCommerce stores:**
- Most stores are guest checkout — there IS no persistent user identifier until purchase (too late for the funnel).
- The strongest cross-device identifier (email) is already sent as `em` via Advanced Matching.
- Platform cookies (`_fbp`, `_ttp`, `_ga`, `_epik`) already handle within-session event linking — a WooCommerce session key would be redundant.
- Social login IDs would be genuinely useful but depend on third-party plugins, making it unreliable as a core feature.
- Hashed WP user ID only covers logged-in users (~10% of typical store traffic).
- Meta's 15.52% claim is an average across ALL advertisers, skewed by high-login-rate sites (SaaS, subscriptions, social platforms). For guest-checkout e-commerce, the real impact is near zero.

**Available identifiers assessed:**

| Identifier | When available | Cross-device? | Already sent elsewhere? |
|---|---|---|---|
| WP user ID (hashed) | Logged-in only | Yes (if they log in) | No |
| WC session key | Always | No | No — but `_fbp` covers this |
| Billing email (hashed) | Purchase only | Yes | Yes — `em` in user_data |
| Platform cookies | When pixel loads | No | Yes — `fbp`, `ttp`, etc. |

**Verdict:** Not implementing. Every useful identifier is either already sent as a dedicated field or has the same limitations as platform cookies. The quality wins that matter are the ones hitting 100% of traffic (dedup accuracy, AJAX delivery, firing precision). Reference this analysis when customers ask about platform match quality scores.

**Facebook Login ID — DISMISSED.** Meta pushing their login ecosystem. Not realistic for a WordPress plugin.

**IPv6 — LOW PRIORITY.** Depends on `unipixel_get_ip_address()` implementation. Minor.

## Cross-Platform — AddToCart Event

### ATC-001: AJAX add-to-cart client pixel never fires — FIXED

**Found during:** AddToCart quality assessment (2026-03-14)
**Files affected:** `woocomm-hook-handling/hook-handlers-addtocart.php`, `woocomm-hook-handling/client-side-send-addtocart.php`, `woocomm-hook-handling/client-side-localize-addtocart.php`

**The problem:** When WooCommerce uses AJAX add-to-cart (default on shop/archive pages in most themes), the `woocommerce_add_to_cart` hook fires inside an AJAX request context. The server event fires correctly. But the client pixel is injected via `wp_add_inline_script('unipixel-common', $script)` — which only outputs when WordPress renders a full HTML page. WooCommerce AJAX add-to-cart returns JSON via `wp_send_json()`, so the inline script never reaches the browser.

**How it was detected in code:** In `hook-handlers-addtocart.php`, `$isAjax = wp_doing_ajax()`. When true, `unipixel_inline_script_*_addtocart('')` was called with empty trigger hook — hitting the `else` branch that does `wp_add_inline_script()` directly during the AJAX context. This did nothing because AJAX returns JSON.

**Impact:** Platforms received server-only AddToCart events for AJAX flows. No corresponding browser pixel hit. Reduced match quality and deduplication was meaningless (only one side fired).

**Fix (2026-03-14):** Implemented `woocommerce_add_to_cart_fragments` filter to deliver pixel scripts via the AJAX JSON response. WooCommerce's JS applies fragments to the DOM and triggers `added_to_cart`. All 5 platforms (Meta, Google, TikTok, Pinterest, Microsoft) now fire client pixels after AJAX add-to-cart.

**How it works:**
- `UniPixel_AddToCart_Fragment_Collector` static class accumulates platform data and server results within the single AJAX request lifecycle
- `hook-handlers-addtocart.php` AJAX branches push client data to the collector instead of calling `wp_add_inline_script()`
- `unipixel_addtocart_fragments()` filter reads the collector and builds a combined `<script>` inside a hidden div (`div.unipixel-addtocart-fragment`)
- Each platform's pixel call is wrapped in an IIFE with inline JSON data (no `wp_localize_script` dependency)
- Console logging for server results is also included in the fragment
- A placeholder `<div class="unipixel-addtocart-fragment">` is output via `wp_footer` so WooCommerce has a target to replace

**Coverage after fix:**

| Scenario | Server event | Client pixel | Mechanism |
|---|---|---|---|
| WooCommerce AJAX add-to-cart (most themes, shop/archive pages) | Hook fires during AJAX | **Fragments** | Fragment injected into AJAX JSON response, WooCommerce JS applies to DOM |
| POST redirect add-to-cart (single product page default) | Hook fires during POST | Transient → next page `wp_footer` | Existing transient relay, unchanged |
| Custom theme AJAX (non-WooCommerce endpoint) | Hook fires during AJAX | Transient fallback on next page | Falls through to existing path |
| WooCommerce Blocks (Store API) | May not fire `woocommerce_add_to_cart` hook | Neither | Known limitation, future work |

---

### ATC-002: Transient user identifier collision risk for guests — PARTIALLY FIXED

**Found during:** AddToCart quality assessment (2026-03-14)
**Files affected:** `woocomm-hook-handling/client-side-send-addtocart.php` (all platform functions)

**The problem:** Guest users are identified by `md5(IP + User-Agent)`. Multiple guests behind the same NAT/VPN with the same browser share a transient key. One user's add-to-cart data could overwrite another's, or fire the wrong pixel on the wrong session.

**Duplicated identifier logic — FIXED (2026-03-14).** All 10 inline `is_user_logged_in() ? get_current_user_id() : md5(IP+UA)` blocks consolidated to use centralized `unipixel_get_user_identifier_for_transient()` from `unipixel-functions.php`.

**Collision risk — OPEN.** The underlying `md5(IP + User-Agent)` approach still has a theoretical collision risk for guests behind the same NAT. A future improvement would be to use `WC()->session->set()` / `WC()->session->get()` instead — WooCommerce sessions are keyed to the actual user session cookie, not IP+UA. Low priority — the AJAX fragments path (ATC-001 fix) bypasses transients entirely for the most common add-to-cart flow, reducing the surface area where this matters.

---

## Release Quality — Recurring Issues

### RQ-001: Smart quotes (Unicode curly quotes) keep entering PHP files

**Occurrences:**
- **v2.5.3:** 15 PHP files shipped with U+2018/U+2019 (single curly quotes). Caused fatal errors on all WooCommerce events. Fixed in v2.5.4.
- **v2.6.0 pre-export:** 4 PHP files had smart quotes again (class-unipixel-log.php, unipixel-functions.php, tiktok-enqueue.php, prepare-common-to-platform-purchase.php). All in comments — no runtime impact, but still a risk.

**How they get in:** Code pasted from tools that auto-convert straight quotes to curly: ChatGPT web UI, Google Docs, Word, Notion, some clipboard managers. The characters are U+2018 `'`, U+2019 `'`, U+201C `"`, U+201D `"`. In PHP string context they cause parse errors. In comments they're inert but signal contamination.

**Pre-export check (mandatory):**
```bash
grep -rl $'\xe2\x80\x98\|\xe2\x80\x99\|\xe2\x80\x9c\|\xe2\x80\x9d' --include="*.php" .
```
Must return empty. If not, fix with:
```bash
sed -i "s/\xe2\x80\x98/'/g; s/\xe2\x80\x99/'/g; s/\xe2\x80\x9c/\"/g; s/\xe2\x80\x9d/\"/g" <files>
```

---

### RQ-002: Stray closing quotes in multiline PHP strings

**Occurrence:** v2.6.0 — `unipixel-functions.php` line 158 had a premature `"` closing the string mid-sentence in the `Google_Gtm` help text. The next line (`<br/><b>3.</b>`) became bare PHP code.

**Why it wasn't caught locally:** `php -l filename` on the unobfuscated multiline source file passed. The WordPress.org SVN pre-commit hook lints via stdin (`php -l` on piped/collapsed content), which is stricter after obfuscation collapses the whitespace.

**How it got in:** Likely a manual edit that split a string across lines and left a stray `"` at the end of one line. It was latent — present in the source for an unknown period.

**Pre-export check (mandatory):** After running `obf.sh export`, lint ALL obfuscated PHP files:
```bash
EXPORT="C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports"
for f in "$EXPORT"/**/*.php "$EXPORT"/*.php; do
  result=$(php -l "$f" 2>&1)
  echo "$result" | grep -qi "error" && ! echo "$result" | grep -q "No syntax errors" && echo "FAIL: $f" && echo "$result"
done
```
Must produce no FAIL output. Also lint via stdin to match the SVN hook behaviour:
```bash
cat "$EXPORT/functions/unipixel-functions.php" | php -l 2>&1
```

> The pre-export checklist itself lives in `app-knowledge/deploy-and-release.md` — this section captures *why* each check is in the list.

---

## Google

### G-001: Mutual-exclusion rule for client-side vs server-side per event

**Rule:** For most event types, Google permits **either** client-side (gtag) **or** server-side (Measurement Protocol) — **not both at the same time**. The exception is **Purchase**, where both client-side and server-side are allowed (and recommended for dedup via `transaction_id`).

**Why:** Google's GA4 attribution does not deduplicate non-purchase events the way Meta CAPI does — sending the same event from both sides counts it twice. Purchase is special-cased because GA4 uses `transaction_id` as a natural dedup key.

**How the plugin implements it:** The admin UI for Google events enforces this — picking client-side disables the server-side toggle for that event type, and vice versa, except on the Purchase event row.

**Other platforms:** Meta, TikTok, Pinterest, Microsoft do **not** have this restriction. They support browser + CAPI on every event, with `event_id` (Meta/TikTok/Pinterest) as the dedup key.

**Implications for testing:**
- Variants of Google-related flows can only test legal admin states. Trying to enable both for a non-purchase event should be blocked by the UI itself (worth its own scenario in `admin-pixel-config`).
- Purchase variants are the only Google flow where "both on" is a valid state.

---

_(No platform reports processed yet)_

## Microsoft

_(No reports processed yet)_

---

## Pinterest

### PIN-001: Custom event tier accepts only 6 sub-types — TERMINOLOGY QUIRK

**Source:** Pinterest Conversions API event-type spec
**Surface impact:** Bespoke event flow (centralised Event Manager + per-platform Custom Events table)

**The quirk:** Pinterest's "custom" event tier is itself a finite list, not a free-form name. Accepted values: `custom`, `lead`, `search`, `signup`, `view_category`, `watch_video`. Anything outside this set is dropped or ignored when sent as the event_name on a Pinterest Tag / CAPI call.

**Implication for our UX:** Bespoke Platform Event References for Pinterest are not truly free-form. A user who types `MyBespokeEvent` for Pinterest will see no data. Either:
- Validate the bespoke value on input against the 6 allowed sub-types and warn, or
- Restrict the dropdown to these 6 values when Pinterest is the active platform context.

**Cross-reference:** `event-terminology.md` § Platform-specific quirks.

---

## Google

### G-002: GA4 event-name validation — INFORMATIONAL

**Source:** GA4 event naming docs
**Surface impact:** Bespoke event flow for Google.

**The rules:** GA4 event names must start with a letter, contain only letters / numbers / underscores, and not exceed 40 chars. Spaces, hyphens, and special characters are rejected.

**Implication:** Our canonical bespoke example `MyBespokeEvent` is technically valid (starts with letter, only letters). It violates GA4's snake_case style convention but is accepted. No validation needed — but worth a soft inline note where space allows.

**Cross-reference:** `event-terminology.md` § Platform-specific quirks.
