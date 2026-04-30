# Flow: WooCommerce — Purchase

**Status:** Draft
**Last run:** —
**Covers:** Order completion → Purchase event fires browser-side on thank-you page AND CAPI server-side. Value, currency, order ID, items all correct. Browser/CAPI dedup matches. Highest-stakes event in the plugin — most important flow to keep passing.

## Setup

- WooCommerce active with a checkout flow that can complete (test gateway like Cheque or "Direct bank transfer" enabled, OR a Stripe/PayPal test mode)
- At least one product in stock
- Consent: granted (full)
- All five platforms enabled with test pixel IDs and CAPI tokens
- Plugin console logging ON
- Hook reference: `woocommerce_thankyou` (priority 100) → `unipixel_woocommerce_handler_purchase`

---

## Scenario 1: Place test order

**Action:**
1. Add product to cart.
2. Navigate to checkout.
3. Fill billing (test data).
4. Choose offline gateway (Cheque / Bank Transfer) — completes synchronously to thank-you page.
5. Submit order.

**Asserts:**
- Thank-you page loads at `/checkout/order-received/{order_id}/?key=...`
- Order created in WooCommerce (visible at `wp-admin/post.php?post={order_id}&action=edit`)

---

## Scenario 2: Purchase events fire on thank-you page

**Action:** On the thank-you page, wait 3s for events to dispatch.

**Asserts:**
- Network: Meta `graph.facebook.com/tr` with `ev=Purchase` seen
- Network: Google GA4 collect with `en=purchase` seen
- Network: TikTok with `event=CompletePayment` (TikTok's purchase event) seen
- Network: Pinterest with `event=checkout` seen
- Network: Microsoft UET purchase event seen
- Browser console (logging on): server-side dispatch results echoed for each platform

**Captures:**
- Meta Purchase payload (browser) → `expected/scenario-2-meta-purchase-browser.json`
- Meta Purchase payload (CAPI) → `expected/scenario-2-meta-purchase-capi.json`
- GA4 purchase payload → `expected/scenario-2-ga4-purchase.json`
- TikTok CompletePayment → `expected/scenario-2-tiktok-completepayment.json`
- Pinterest checkout → `expected/scenario-2-pinterest-checkout.json`
- Microsoft purchase → `expected/scenario-2-microsoft-purchase.json`

---

## Scenario 3: Payload correctness — order data

For each platform's captured payload, assert:

- `value` = order total (matches WooCommerce admin order page)
- `currency` = WooCommerce currency code (e.g. `AUD`, `USD`)
- `order_id` / `transaction_id` / `eventID` includes the WC order number
- `items` / `content_ids` / `contents` array includes all line items with correct IDs, quantities, prices
- `num_items` matches sum of line item quantities
- (Meta) `user_data` has hashed email, phone (if billing fields filled)
- (Google) `transaction_id` = order ID (this is GA4's dedup mechanism)

---

## Scenario 4: Browser/CAPI dedup

**Asserts:**
- Meta `event_id` matches browser ↔ CAPI exactly
- TikTok `event_id` matches
- Pinterest `event_id` matches
- Google: `transaction_id` matches between browser and Measurement Protocol payloads
- Microsoft: dedup mechanism `[TBD: confirm]`

---

## Scenario 5: Refresh thank-you page does NOT double-fire

**Action:** Reload the order-received page once.

**Asserts:**
- No new Purchase events fire (plugin must guard against double-dispatch — common bug)
- Network shows no second wave of Meta/GA4/TikTok/Pinterest/Microsoft purchase requests
- If plugin uses a session flag or order meta to prevent re-fire, confirm flag exists

**Known gap:** This is a classic regression area. Worth scenario-specific attention. The implementation uses `unipixel_woocommerce_handler_purchase` on `woocommerce_thankyou` — confirm dedup-on-reload mechanism.

---

## Scenario 6: Stored Event Log

**Action:** Navigate to wp-admin Event Logs.

**Asserts:**
- Purchase rows for each enabled platform in the last 60s
- Each row's `order_id` / event payload matches the test order
- Platform response shows success (HTTP 200, no error in response body)

---

## Known gaps

- Async gateways (Stripe, PayPal redirect): order completion happens via webhook after thank-you redirect — separate flow needed. The classic `woocommerce_thankyou` only fires when the user lands on the page; if they don't return, Purchase fires from `woocommerce_order_status_completed` (or similar) instead. Confirm plugin behaviour.
- Refunds / order cancellation: do they trigger any CAPI cleanup events? Probably not, but worth confirming.
- Multi-currency stores: separate flow.
- Subscriptions / recurring: separate flow.
