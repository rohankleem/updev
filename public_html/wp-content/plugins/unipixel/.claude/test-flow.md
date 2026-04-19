# UniPixel — End-to-End Test Flow

> Run this flow after any release or significant change to verify all pixel events fire correctly.

---

## Prerequisites

1. **Local dev site running:** `https://sheds.local.site/`
2. **WooCommerce active** with at least one purchasable product
3. **Non-card payment method enabled** (Check payments or Cash on Delivery) — avoids real payment processing
4. **UniPixel active** with at least one platform configured (pixel ID + access token)
5. **Browser dev tools or Chrome MCP extension** for monitoring console/network

### WooCommerce payment setup notes

- **Check payments:** WooCommerce > Settings > Payments > Check payments — enable, no restrictions needed
- **Cash on Delivery:** If using COD, clear "Enable for shipping methods" field (leave blank = works for all methods). COD can be restricted by shipping method and won't appear if the customer's shipping method isn't in the allowed list.

---

## Test Product

**URL:** `https://sheds.local.site/product/steel-dog-kennel-gable-roof/?attribute_pa_size=small`
**Price:** ~$1 (safe for test orders)

If this product isn't available, use any WooCommerce product. The key requirement is a low-price item to minimise test order impact.

---

## Test Steps

### Step 1: Product Page — ViewContent + PageView

1. Navigate to the product page URL
2. **Verify PageView fires** (client-first pattern):
   - Console: `ttq.track('PageView')`, `uetq.push(...)`, `gtag(...)` calls
   - Network: requests to `analytics.tiktok.com`, `bat.bing.com`, `google-analytics.com`
   - Both TikTok and Microsoft should fire within seconds of page load
3. **Verify ViewContent fires** (server-first pattern for WooCommerce product pages):
   - Check `window.UniPixelViewContentTikTok` and `window.UniPixelViewContentMicrosoft` exist
   - Both should contain `event_id`, `value`, `currency`, `content_ids`
   - `event_id` must match between platforms (deduplication)

### Step 2: Custom Click Event — Configurator Button

1. Click "Start The Price Configurator" button on the product page
2. **Verify custom click event fires:**
   - TikTok: `testClickOpenConfigurator_TikTok` event logged
   - Microsoft: `btnGetPriceModalTest` event name, fired via `uetq.push` with `ea=btnGetPriceModalTest`
   - Google: `getprice_clickopen` event via GTM
3. Network: AJAX callback to `admin-ajax.php` (client-first pattern — JS fires pixel, then AJAX to server)

### Step 3: Add to Cart — AddToCart

1. Select required options (e.g., Assembly Options: "I will assemble myself")
2. Click "Add to cart"
3. **Verify AddToCart fires** (server-first pattern):
   - Check `window.UniPixelAddToCartTikTok` and `window.UniPixelAddToCartMicrosoft`
   - Both should contain matching `event_id`, plus `value`, `currency`, `content_ids`
   - TikTok event name: `AddToCart`
   - Microsoft event name: `add_to_cart`

### Step 4: Checkout Page — InitiateCheckout / begin_checkout

1. Proceed to checkout
2. **Verify InitiateCheckout fires** (server-first pattern):
   - Check `window.UniPixelInitiateCheckoutTikTok` and `window.UniPixelCheckoutMicrosoft`
   - Note: TikTok uses `InitiateCheckout`, Microsoft uses `begin_checkout`
   - Both should contain matching `event_id`
3. Fill in billing details:
   - Name: Test User (or real billing address)
   - Address: 123 Test Street
   - City/State/Postcode: Melbourne VIC 3000
   - Phone: 0400000000
   - Email: test@example.com

### Step 5: Place Order — Purchase

1. Select "Check payments" (or Cash on Delivery) as payment method
2. Click "Place Order"
3. Wait for order confirmation page (`/checkout/order-received/{order_id}/`)
4. **Verify Purchase fires** (server-first pattern):
   - Check `window.UniPixelPurchaseTikTok` and `window.UniPixelPurchaseMicrosoft`
   - Both should contain:
     - `event_id` — must match between platforms (deduplication)
     - `value` — order total (e.g., "1")
     - `currency` — "AUD"
   - TikTok event name: `Purchase`
   - Microsoft event name: `purchase`
5. Pixel objects should exist on page: `window.ttq`, `window.uetq`

### Step 6: Verify Stored Event Logs

1. Navigate to **wp-admin > UniPixel > Stored Event Logs**
2. Confirm all events from the test run are logged in order:

| Expected Event | TikTok Name | Microsoft Name | Trigger |
|---|---|---|---|
| ViewContent | ViewContent | view_item | WooCommerce View Content Hook |
| AddToCart | AddToCart | add_to_cart | WooCommerce Add To Cart Hook |
| Custom click | testClickOpenConfigurator_TikTok | btnGetPriceModalTest | click |
| InitiateCheckout | InitiateCheckout | begin_checkout | WooCommerce Visit Checkout Hook |
| Purchase | Purchase | purchase | WooCommerce Purchase Hook |
| PageView (multiple) | PageView | PageView | shown |

3. Each WooCommerce event should show:
   - **Platform:** TikTok / Microsoft (one row each)
   - **Send Method:** `client` (or `server` if server-side is enabled)
   - **Party:** `third`
   - **Event Trigger:** The relevant WooCommerce hook name

---

## What to Check For

### Deduplication
- Every WooCommerce event (AddToCart, ViewContent, Checkout, Purchase) must have the **same `event_id`** across all platforms
- This ensures platforms don't double-count when both client and server fire

### Event naming per platform

| Generic Event | TikTok | Microsoft | Google (GA4) | Meta | Pinterest |
|---|---|---|---|---|---|
| PageView | PageView | pageLoad | page_view | PageView | page_visit |
| ViewContent | ViewContent | view_item | view_item | ViewContent | view_content |
| AddToCart | AddToCart | add_to_cart | add_to_cart | AddToCart | add_to_cart |
| Checkout | InitiateCheckout | begin_checkout | begin_checkout | InitiateCheckout | initiate_checkout |
| Purchase | Purchase | purchase | purchase | Purchase | checkout |

### Server-side sends
- If `serverside_global_enabled` is ON and platform has valid credentials, events should also show `server` send method in logs
- Server response column should show HTTP response from platform API
- Currently on dev: server-side is not active (no live credentials), so all events show "Client-side event, no response"

### Google tracking note
- On sheds.local.site, Google tracking runs via **GTM** (`GTM-T6M6CQP`), not via UniPixel's Google integration
- Google events won't appear in UniPixel stored logs — check via browser network requests to `google-analytics.com` instead

---

## Cleanup After Testing

- Cancel test orders in WooCommerce > Orders (mark as Cancelled or Trash)
- Optionally clear UniPixel stored event logs if they're cluttering the view

---

## Quick Smoke Test (5 minutes)

If a full checkout isn't needed, this abbreviated flow covers the critical paths:

1. Visit any product page → verify PageView fires for all platforms
2. Click the configurator button → verify custom click event fires
3. Check Stored Event Logs → confirm events are being recorded

This tests: pixel initialization, client-first event pattern, custom event configuration, and database logging.

---

## Test Results Log

### 2026-03-15 — v2.6.0 (Full E2E)

**Platforms tested:** TikTok (ID 3), Microsoft/UET (ID 5)
**Payment method:** Check payments
**Product:** Steel Dog Kennel Gable Roof (small) — $1

| Event | TikTok | Microsoft | Dedup event_id |
|---|---|---|---|
| PageView | ✅ | ✅ | n/a (client-first) |
| ViewContent | ✅ | ✅ | ✅ matched |
| Custom click (configurator) | ✅ | ✅ | n/a (client-first) |
| AddToCart | ✅ | ✅ | ✅ matched |
| InitiateCheckout | ✅ | ✅ | ✅ matched |
| Purchase | ✅ `purchase_1773473275.039` | ✅ `purchase_1773473275.039` | ✅ matched |

**Stored Event Logs:** All events logged correctly with correct platform names, triggers, and timestamps.
**Server-side sends:** Not active on dev (no live API credentials). Client-side pipeline fully verified.
**Verdict:** All events passing. v2.6.0 ready for export.
