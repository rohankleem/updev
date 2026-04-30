# Flow: WooCommerce — Add to Cart

**Status:** Draft
**Last run:** —
**Covers:** Click "Add to cart" on a product → AddToCart event fires browser-side and CAPI server-side for each enabled platform. Dedup IDs match.

## Setup

- WooCommerce active with at least one published, in-stock product
- Test product slug: `[TBD: confirm or create]`
- Consent: granted (full)
- All five platforms enabled
- Plugin console logging ON
- Hook reference: `woocommerce_add_to_cart` (priority 100) → `unipixel_woocommerce_handler_add_to_cart`

---

## Scenario 1: Navigate to product page

**Action:** Navigate to `/product/{test-product-slug}/`. Wait for page ready.

**Asserts:**
- Add to Cart button present (selector typically `button.single_add_to_cart_button` or `.ajax_add_to_cart`)
- ViewContent fires (covered by woocommerce-viewcontent flow — not asserted here, but capture network state for diff)

---

## Scenario 2: Click Add to Cart (AJAX path)

**Action:** Click the Add to Cart button. WooCommerce uses AJAX add-to-cart on archive pages and a form submit on single product pages — `[confirm which path runs on first run; this scenario assumes AJAX]`. Wait 3s for events to dispatch.

**Asserts:**
- WooCommerce AJAX request to `?wc-ajax=add_to_cart` returns 200
- Network: Meta `graph.facebook.com/tr` with `ev=AddToCart` seen
- Network: Google GA4 collect with `en=add_to_cart` seen
- Network: TikTok with `event=AddToCart` seen
- Network: Pinterest with `event=addtocart` seen
- Network: Microsoft UET event seen
- Cart fragment updated (cart count visible on page increased) — confirms WC flow completed

**Captures:**
- Meta AddToCart payload (browser) → `expected/scenario-2-meta-addtocart-browser.json`
- Meta AddToCart payload (CAPI) → `expected/scenario-2-meta-addtocart-capi.json`
- GA4 add_to_cart → `expected/scenario-2-ga4-addtocart.json`
- TikTok AddToCart → `expected/scenario-2-tiktok-addtocart.json`
- Pinterest addtocart → `expected/scenario-2-pinterest-addtocart.json`
- Microsoft AddToCart → `expected/scenario-2-microsoft-addtocart.json`

---

## Scenario 3: Browser/CAPI dedup

**Asserts:**
- Meta `event_id` matches between browser and CAPI fixtures
- TikTok `event_id` matches
- Pinterest `event_id` matches
- All platforms received the event within 5s of each other (by timestamp)

---

## Scenario 4: Payload correctness

**Asserts:**
- Each platform's payload contains:
  - product ID(s)
  - product name(s)
  - quantity (1 unless flow varies it)
  - value = unit price × quantity
  - currency = WooCommerce currency
- Consistent values across platforms (same product, same totals, same currency)

---

## Scenario 5: Form-submit path on single product page

**Action:** Reset cart. Reload product page. Submit the add-to-cart form (non-AJAX path) — form submission typically reloads page.

**Asserts:**
- After page reload: events still fire (plugin handles non-AJAX add-to-cart via session/transient → next pageview)
- Same network requests + dedup behaviour as Scenario 2
- `[Confirm on first run: does plugin handle the non-AJAX path correctly?]`

---

## Known gaps

- Whether the plugin distinguishes between AJAX add-to-cart and form-submit add-to-cart in handling — code reference: `client-side-send-addtocart.php:54` uses `woocommerce_add_to_cart_fragments` filter, suggesting AJAX path. Form-submit may need its own scenario.
- Variable products: separate scenario for variant selection.
- Multiple quantities: separate scenario.
