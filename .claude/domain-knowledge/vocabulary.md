# Vocabulary — UniPixel domain terms

Pinned definitions for the tracking / ad-platform domain this plugin operates in. When a term is used in any other doc, it carries the meaning defined here.

For operating-manual terms (plugin, host, session, project, etc.) see `/CLAUDE.md` § Glossary.

---

> **Event terminology framework:** the structural rules (hierarchy, ordering, copy patterns, per-platform hints) live in `event-terminology.md`. Pinned short definitions live below.

## Core tracking concepts

| Term | Definition |
|---|---|
| **Event** | A discrete action we tell an ad platform about: PageView, ViewContent, AddToCart, InitiateCheckout, Purchase, or a site event. The umbrella term — preferred over "conversion" in structural naming. |
| **WooCommerce events** | Events fired automatically from WooCommerce hooks (AddToCart, InitiateCheckout, Purchase, etc.). Cross-platform by nature — one hook fires every enabled platform. Managed with toggles + Apply Recommended Settings on each platform's Events Setup page. |
| **Site events** | Events the user configures themselves on non-WooCommerce interactions: clicks, visible elements, page URLs, form submissions on URL. The umbrella term in admin UI (replaces "Custom Events" as of 2026-05-03). External surfaces (marketplace, blog, ads) still say "Custom Events" for SEO recognition — see `event-terminology.md`. |
| **Standard event** | A site event whose Platform Event Reference is from that platform's recognised list (Meta `Lead`, Google `generate_lead`, etc.). Recognised by the platform's Events Manager. |
| **Bespoke event** | A site event with a free-form Platform Event Reference. Recorded by the platform but not tracked as a known event type. Replaces "Custom event" in the free-form tier specifically — disambiguates the previously overloaded "custom". |
| **Trigger** | When a site event fires: `click`, `shown`, `url`, `form_submit_on_url`. |
| **Trigger Target** | The thing the trigger acts upon. CSS selector for click/shown; URL pattern for url/form_submit_on_url. Replaces "Element Reference". Structural / docs term — pairs with "Trigger" when explaining the data model. **User-facing label is "Acts On"** (column headers + form labels) for plainer English. |
| **Platform Event Reference** | The string sent to the platform's pixel/CAPI as the event identifier. Full prose: "Platform Event Reference". Column headers: "Platform Event Ref" (keep the "Platform" prefix; only abbreviate "Reference" to "Ref"). |
| **Cross-platform event** | An event configured once that creates linked rows across multiple platforms. Replaces "conversion" in the centralised builder context. |
| **Conversion** | Reserved for marketing copy and SEO. Not used in admin labels or doc field names. The industry is collapsing "conversions" back into "events" — keep our structural vocabulary aligned. |
| **Pixel** | The client-side JS that fires events from the browser (e.g. `fbq()`, `ttq.track()`, `gtag()`, `uetq.push()`). Each platform has its own pixel. |
| **CAPI / Conversions API** | A platform's server-to-server API for receiving events. Meta CAPI, Pinterest CAPI, TikTok Events API, Microsoft UET CAPI, GA4 Measurement Protocol. UniPixel calls these from the WordPress server. |
| **Server-first** | Event flow where the server fires first (via CAPI), then inline JS fires the browser pixel with a matching eventId. Used for WooCommerce events. |
| **Client-first** | Event flow where the browser pixel fires first, then an AJAX call relays the same eventId to the server to fire the CAPI call. Used for PageView and custom click events. |
| **eventId** | The deduplication identifier. Same value must appear on both the browser-fired pixel event and the server-fired CAPI event, so the platform counts the conversion once. Generated once per event and propagated — in PHP as `purchase_<microtime>` for WooCommerce, in JS as `event_<timestamp>` for client-first. |
| **Dedup / Deduplication** | The platform recognising that a browser event and a server event are the *same* event, via matching eventId. Without it, platforms count conversions twice. |

---

## Click IDs & attribution

| Term | Definition |
|---|---|
| **Click ID** | A unique identifier added to a URL when a user clicks an ad, so the conversion can be traced back to the specific ad click. Each platform has its own. |
| **fbclid** | Meta's click ID query param. Captured from the landing URL into cookie `unipixel_fbclid` (90-day retention). Used to synthesise `fbc`. |
| **fbc** | The Meta-formatted click identifier sent in CAPI: `fb.<domainIndex>.<timestamp>.<fbclid>`. |
| **fbp** | Meta's browser-pixel-set cookie. Used for view-through attribution. Cannot be set server-side — if the browser pixel is blocked, `_fbp` doesn't exist. |
| **gclid** | Google Ads click ID. |
| **ttclid** | TikTok click ID. |
| **msclkid** | Microsoft Ads click ID. Captured with 90-day retention. |
| **epik** | Pinterest click ID. |

---

## User data (for matching)

| Term | Definition |
|---|---|
| **Advanced Matching** | Hashed PII (email, phone, name, city, zip, etc.) sent alongside events to help platforms match conversions back to users. UniPixel sends these for WooCommerce events where customer data is available. |
| **em / ph / fn / ln / ct / st / zp** | Hashed email, phone, first name, last name, city, state, zip — standard Meta CAPI user_data keys. |
| **external_id** | A persistent user identifier the advertiser provides (e.g. hashed WordPress user ID). Assessed and deprioritised for UniPixel — see `platform-discoveries.md` § META-002. |

---

## Consent & privacy

| Term | Definition |
|---|---|
| **CMP** | Consent Management Platform. UniPixel reads state from 9 integrations (OneTrust, Cookiebot, Complianz, CookieYes, Osano, Silktide, Orest Bida, Moove GDPR) or serves its own built-in popup. |
| **Consent mode** | How platforms expect consent signals to be communicated. `adStorageConsent: granted/denied` is sent server-side; UET has its own consent API client-side. |
| **Consent already checked** | Flag passed through the event pipeline to avoid double-checking consent in deeper layers. An argument in `unipixel_send_server_event_*()`. |

---

## Release, distribution & protection

| Term | Definition |
|---|---|
| **Obfuscation** | Hex-encoding PHP string literals, minifying JS/CSS, stripping comments. Runs via `_obf/obf.sh`. Layer-1 protection against casual theft. |
| **SVN working copy** | The local folder the obfuscation export writes into. Also the wordpress.org plugin SVN checkout. TortoiseSVN commits from here to wordpress.org. Path: `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`. |
| **Stable tag** | The `Stable tag:` line in `readme.txt`. Must match the version in `unipixel.php`. wordpress.org reads this to decide which version users receive. |
| **License-gated updates** | The planned primary protection strategy: updates require a valid license, no license = no new versions. Details in `licensing-and-protection.md`. |

---

## WooCommerce specifics

| Term | Definition |
|---|---|
| **WC session** | The WooCommerce-managed session cookie. Survives until the WC session expires (default ~2 days). More reliable than `md5(IP+UA)` for keying transients. |
| **Fragment (`woocommerce_add_to_cart_fragments`)** | WooCommerce's mechanism for updating parts of the DOM after an AJAX add-to-cart. UniPixel uses it to deliver client pixels after AJAX add-to-cart (where `wp_add_inline_script` would do nothing — no HTML returned). |
| **Transient relay** | The pattern of storing client pixel data in a WP transient keyed by `unipixel_get_user_identifier_for_transient()`, then outputting it via `wp_footer` on the next page load. Used for POST-redirect flows. Superseded for AJAX flows by the fragment mechanism. |

---

## Platform-specific event names

| Generic | Meta | Google (GA4) | TikTok | Pinterest | Microsoft |
|---|---|---|---|---|---|
| PageView | PageView | page_view | PageView | page_visit | pageLoad |
| ViewContent | ViewContent | view_item | ViewContent | view_content | view_item |
| AddToCart | AddToCart | add_to_cart | AddToCart | add_to_cart | add_to_cart |
| InitiateCheckout | InitiateCheckout | begin_checkout | InitiateCheckout | initiate_checkout | begin_checkout |
| Purchase | Purchase | purchase | Purchase | checkout | purchase |
