# Flow: WooCommerce — ViewContent

**Status:** Draft
**Last run:** —
**Covers:** Visiting a single product page fires `ViewContent` (or platform equivalent) browser-side and CAPI server-side for each enabled platform. Browser and server `event_id` match for dedup.

## Setup

- WooCommerce active with at least one published product
- Test product slug: `[TBD: confirm or create test product]`
- Consent: granted (full)
- All five platforms enabled with test pixel IDs and CAPI tokens
- Plugin console logging ON
- Hook reference: `woocommerce_before_single_product` → `unipixel_woocommerce_handler_viewcontent`

---

## Scenario 1: Navigate to single product page

**Action:** Navigate to `https://updev.local.site/product/{test-product-slug}/`. Wait `DOMContentLoaded` + 3s.

**Asserts:**
- Network: Meta `graph.facebook.com/tr` request with `ev=ViewContent` seen
- Network: Google GA4 collect with `en=view_item` seen
- Network: TikTok pixel with `event=ViewContent` (TikTok event name) seen
- Network: Pinterest with `event=pagevisit` (Pinterest doesn't have native ViewContent — confirm behaviour) seen
- Network: Microsoft UET event seen
- Browser console (with logging on) shows server-side dispatch results for each enabled platform

**Captures:**
- Meta ViewContent payload (browser) → `expected/scenario-1-meta-viewcontent-browser.json`
- Meta ViewContent payload (CAPI server-side, from console log or Event Log) → `expected/scenario-1-meta-viewcontent-capi.json`
- GA4 view_item payload → `expected/scenario-1-ga4-viewitem.json`
- TikTok ViewContent payload → `expected/scenario-1-tiktok-viewcontent.json`
- Pinterest ViewContent equivalent → `expected/scenario-1-pinterest-viewcontent.json`
- Microsoft payload → `expected/scenario-1-microsoft-viewcontent.json`

---

## Scenario 2: Browser/server dedup

**Action:** From the captures in Scenario 1, compare `event_id` (Meta) / `transaction_id` / `event_name`+timestamp pair (per platform) between browser and CAPI payloads.

**Asserts:**
- Meta: `event_id` is identical between browser-side and CAPI payloads (same event)
- TikTok: `event_id` identical
- Pinterest: `event_id` identical (if used)
- Google: client_id consistent; for MP `transaction_id` not applicable to ViewContent — confirm dedup mechanism
- Microsoft: confirm dedup mechanism

---

## Scenario 3: Product data correctness

**Action:** Inspect captured payloads.

**Asserts:**
- Meta `custom_data.content_ids` = `[product_id]`
- Meta `custom_data.content_name` = product title
- Meta `custom_data.value` = product price
- Meta `custom_data.currency` = WooCommerce currency code
- GA4 `items[0].item_id`, `item_name`, `price`, `currency` correct
- Same fidelity check for TikTok, Pinterest, Microsoft

---

## Scenario 4: Stored Event Log entry

**Action:** Navigate to wp-admin Event Logs.

**Asserts:**
- Most recent rows include CAPI ViewContent dispatches for each enabled platform within the last 60s
- Each row's payload matches captured CAPI fixture

---

## Known gaps

- Pinterest equivalent of ViewContent — verify event name on first run (`pagevisit`? `viewcategory`?).
- Whether `value` is sent as a number or string — confirm and assert.
- Variable products: separate flow may be needed (variant ID vs parent ID in payload).
