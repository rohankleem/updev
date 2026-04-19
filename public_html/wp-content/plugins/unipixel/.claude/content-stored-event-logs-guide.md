# How to Use UniPixel's Stored Event Logs

> **Purpose:** Help/docs page for unipixelhq.com. Written for store owners, not developers. References screenshots that need to be created from annotated captures.

---

## Page Title: Stored Event Logs — See Exactly What Your Tracking Sends

## Meta Description: UniPixel stores every event it sends to your ad platforms. Learn how to use Stored Event Logs to verify purchases, debug tracking issues, and see the exact data Meta, Google, TikTok, Pinterest and Microsoft receive.

---

## Article Content

### Every event UniPixel sends is recorded

When UniPixel fires a conversion event — a purchase, an add-to-cart, a page view — it doesn't just send the data and hope for the best. It stores a complete record of what was sent, when it was sent, which platform received it, and whether the platform confirmed it arrived.

This is your Stored Event Logs. It's the single most useful tool you have for understanding what your ad platforms are actually seeing.

**[IMAGE: UniPixel home dashboard with arrow pointing to "View Stored Event Logs" → Open Logs button]**

To access it: go to **UniPixel → Stored Event Logs** in your WordPress admin. Or from the UniPixel home screen, click **Open Logs**.

---

### What you're looking at

Each row in the log is one event sent to one platform. A single purchase on your store generates multiple rows — one per platform, and for most platforms, both a server-side and a client-side entry.

Here's what each column tells you:

| Column | What it means |
|---|---|
| **Log Time** | When the event was sent |
| **Event Name** | What happened — Purchase, AddToCart, page_view, etc. |
| **Platform** | Which ad platform received it — Meta, Google, TikTok, Pinterest, or Microsoft |
| **Send Method** | `server` = sent from your server via the platform's API. `client` = sent from the visitor's browser via the pixel |
| **Party** | `first` = server-side (your server sent it directly). `third` = client-side (the browser sent it) |
| **Event Trigger** | What caused the event — usually "WooCommerce Purchase Hook" for purchases |
| **Response** | What the platform said back. "Successful: Code 200, Ok" means it arrived. "Client-side event, no response" is normal for browser-sent events (the browser pixel doesn't return a response to WordPress) |
| **Sent Data** | The blue info icon — click or hover to see the **exact payload** that was sent |

---

### Finding specific events

Use the filters at the top of the page to narrow down what you're looking for.

**[IMAGE: Filter bar with "purchase" typed in Event Name field, Platform set to "All platforms", with arrow pointing to Filter button]**

**Common searches:**

- **Find a specific purchase:** Type `purchase` in the Event Name filter. You'll see every purchase event across all platforms.
- **Check one platform:** Set the Platform dropdown to `Meta` (or any platform) to see only events sent to that platform.
- **Server-side only:** Set Method to `server` to see only the events sent via the platform's API (bypassing the browser).
- **Client-side only:** Set Method to `client` to see only the events sent via the browser pixel.

The log shows the 200 most recent entries matching your filters.

---

### The real power — seeing exactly what was sent

This is where it gets interesting. Every row has a blue info icon in the **Sent Data** column. Click it, and you'll see the complete data payload that was sent to that platform.

**[IMAGE: Server-side Meta Purchase event with Sent Data popover open, showing the full JSON payload with arrows pointing to key fields: user_data, fbp, em (hashed email), custom_data with value and currency]**

**For a server-side Meta Purchase event, you'll see:**

- **event_name** — "Purchase"
- **event_time** — the exact Unix timestamp
- **event_id** — the deduplication ID (matches the client-side event so Meta counts it once)
- **user_data** — the visitor's data sent to Meta:
  - `client_ip_address` — their IP
  - `client_user_agent` — their browser
  - `fbp` — Meta's browser cookie (used for view-through attribution)
  - `fbc` — the Facebook click ID (used to link this purchase back to a specific ad click)
  - `em`, `ph`, `fn`, `ln` — hashed email, phone, first name, last name (Advanced Matching)
  - `ct`, `st`, `zp`, `country` — hashed city, state, zip, country
- **custom_data** — the transaction details:
  - `currency`, `value` — the order total
  - `transaction_id` — your WooCommerce order number
  - `contents` — product IDs, quantities, and prices

**[IMAGE: Client-side Meta Purchase event with Sent Data popover open, showing the simpler client payload with fbp, email_hashed, phone_hashed]**

**For a client-side Meta Purchase event, you'll see a simpler payload** — the browser pixel sends less data than the server-side call. This is normal. The server-side event is the more complete one.

---

### What to look for when debugging

**"My purchase isn't showing in Meta Ads Manager"**

1. Filter by `purchase` and platform `Meta`
2. Find the purchase by date
3. Check the **Response** column — does it say "Successful: Code 200, Ok"? If yes, Meta received it.
4. Click the Sent Data icon on the **server-side** (first party) event
5. Look for `fbc` in the `user_data` — **this is the field that links the purchase to a specific ad click.** If `fbc` is empty, Meta received the purchase but can't attribute it to your campaign. This happens when the visitor didn't click through from an ad (they may have seen the ad but arrived at your site separately).

**"My events aren't being logged at all"**

- Check that **database storage is enabled** in General Settings
- Check that **Log Server-side Response** is toggled ON for the events you want to log (found on each platform's Events page)
- Client-side events always show "Client-side event, no response" — that's normal, not an error

**"I see Successful: Code 200 but the platform shows no events"**

- The event was sent and accepted. Check the platform's own event testing tools (Meta Events Manager, GA4 DebugView, TikTok Events Manager) to confirm arrival
- There may be a delay — platforms can take minutes to hours to process events
- Check that your Pixel ID / Measurement ID matches what the platform expects

---

### Understanding server vs client events

For each purchase on your store, you'll typically see **two rows per platform** — one server, one client. This is intentional.

**Server-side (first party):** Your WordPress server sends the data directly to the platform's API. This bypasses ad blockers, browser privacy restrictions, and iOS limitations. It's the more reliable path and carries more data (including hashed PII for Advanced Matching).

**Client-side (third party):** The visitor's browser fires the pixel (fbq, gtag, ttq, etc.). This is the traditional tracking method. It can be blocked by ad blockers or privacy tools, but when it works, it provides real-time browser context.

**Both send the same `event_id`** — the platform uses this to deduplicate. If it receives both, it counts the conversion once, not twice. This is automatic. You don't need to do anything.

---

### Quick reference — response codes

| Response | What it means |
|---|---|
| Successful: Code 200, Ok | Event received and accepted |
| Successful: Code 204, Ok | Event received and accepted (no content returned — normal for some platforms) |
| Client-side event, no response | Normal — browser pixels don't send a response back to WordPress |
| Error: Code 400 | Bad request — something in the payload was wrong (check Sent Data for clues) |
| Error: Code 401/403 | Authentication failed — check your access token |

---

### Tips

- **Check your logs after enabling a new platform** — send a test order and verify both server and client events appear with successful responses
- **Use the Sent Data popover to verify Advanced Matching** — look for `em` (email), `ph` (phone) in the user_data. If these are present and hashed, your match quality scores should improve
- **Compare server vs client payloads** — the server-side event should contain more data. If your server-side payload is missing fields the client-side has, something may need configuring
- **Bookmark this page during campaign launches** — when you're running ads and want to verify conversions are being attributed correctly, this is where you check

---

## Image List (to create)

1. **Dashboard → Open Logs** — UniPixel home screen with arrow to "View Stored Event Logs" card
2. **Filter bar** — Stored Event Logs with "purchase" in the Event Name filter, arrow to Filter button
3. **Server-side popover** — Meta server Purchase event (Send Method: server, Party: first) with Sent Data popover open, key fields highlighted (user_data, fbp, fbc, em, custom_data)
4. **Client-side popover** — Meta client Purchase event (Send Method: client, Party: third) with Sent Data popover open, simpler payload visible

> **Note:** The screenshots you already made (annotated with red arrows and callout boxes) cover images 1, 2, 3, and 4 almost exactly. Just need to clean up the personal data (blur IP, truncate hashes) and they're ready to publish.
