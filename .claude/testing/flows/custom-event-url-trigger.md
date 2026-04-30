# Flow: Custom event — URL trigger

**Status:** Draft
**Last run:** —
**Covers:** Phase 1 of [centralised-event-builder](../../projects/centralised-event-builder.md). Custom event configured with `url` trigger fires browser-side and CAPI server-side when URL matches the configured pattern. Fire-once-per-session is honoured.

## Setup

**Required state delta from baseline:**
- Custom event configured for at least one platform (start with Meta) via per-platform admin:
  - `event_trigger` = `url`
  - `element_ref` = `/thank-you*` (URL pattern)
  - `event_name` = `Lead` (or platform-appropriate)
  - `send_client = 1`, `send_server = 1`, `send_server_log_response = 1`
- Meta platform: `serverside_global_enabled = 1` (per-platform server toggle, required for the AJAX endpoint to actually dispatch CAPI)
- Consent: granted (full)
- Plugin console logging ON
- A WordPress page exists at `/thank-you/` (create if needed)
- A WordPress page exists at `/sample-page/` (a known non-matching URL)

## Architectural notes (for the agent)

URL-trigger events fire via the same AJAX path as every other custom event:

1. Browser loads the page. The platform watcher (`clientfirst-watch-and-send-{platform}.js`) iterates `eventsToTrack`.
2. For events with `trigger === "url"`, the watcher calls `window.unipixelMatchUrlPattern(event.elementRef, window.location.href)`.
3. If matched: calls `clientFirstEventTriggered_<Platform>(event, null)` — the same dispatch function used by click/shown.
4. That function:
   - Fires the browser-side pixel if `send_client === 1`
   - POSTs to `ajax_data_for_server_event_{platform}` if `send_server === 1`
   - The AJAX endpoint then dispatches CAPI server-side

There is currently NO direct PHP-on-pageload dispatch path. CAPI for url-trigger always goes through the browser → AJAX → server chain. Tests should verify both the browser pixel and the CAPI request fire correctly via these mechanisms.

---

## Scenario 1: Pageload at matching URL fires event

**Action:** Navigate to `https://updev.local.site/thank-you/` in fresh session.

**Asserts:**
- Browser pixel fires: network request to `graph.facebook.com/tr` with `ev=Lead` (or chosen event_name) within 3s of `DOMContentLoaded`
- AJAX call: POST to `admin-ajax.php` with `action=ajax_data_for_server_event_meta` and `eventName=Lead`
- AJAX response: `dataSent` is truthy (server dispatched CAPI)
- Browser console (logging on): server-side dispatch result echoed
- Stored Event Log (`wp_unipixel_event_log`) has at least one row with `event_name=Lead` and `method=server` within 60s
- Browser-side and server-side rows in Event Log share the same `event_id`

**Captures:**
- Meta browser pixel payload (`graph.facebook.com/tr` request) → `expected/scenario-1-meta-lead-browser.json`
- Meta AJAX request payload (`admin-ajax.php` POST body) → `expected/scenario-1-meta-lead-ajax-request.json`
- Stored Event Log row for the server-side dispatch → `expected/scenario-1-meta-lead-capi-log.json`

---

## Scenario 2: Pageload at non-matching URL does NOT fire

**Action:** Navigate to `https://updev.local.site/sample-page/`.

**Asserts:**
- No `Lead` event request to any platform
- Stored Event Log has no Lead row from this navigation
- Other events (PageView etc.) fire normally — confirming no general suppression

---

## Scenario 3: Wildcard match — query string

**Action:** Navigate to `https://updev.local.site/thank-you/?form=contact&utm_source=fb`.

**Asserts:**
- Lead event fires (URL pattern `/thank-you*` matches path + query)
- Payload identical (or near-identical) to Scenario 1, save for the URL field which now contains query string

**Captures:**
- Diff against Scenario 1 captures — confirm only URL-related fields differ

---

## Scenario 4: Browser/CAPI dedup

**Asserts (from Scenario 1 captures):**
- `event_id` matches between browser and CAPI Meta payloads
- Same dedup integrity for TikTok, Pinterest if configured

---

## Scenario 5: Fire-once-per-session (default ON)

**Action:** After Scenario 1, reload `/thank-you/` once.

**Asserts:**
- No second browser pixel request to `graph.facebook.com/tr` (or other platforms)
- No second AJAX call to `admin-ajax.php` for `ajax_data_for_server_event_meta`
- No second CAPI row in Stored Event Log
- `sessionStorage` contains a key like `unipixel_url_fired:meta:Lead:/thank-you*` set to `'1'` (key format from `window.unipixelShouldFireUrlEvent`)
- Closing the browser tab and re-opening starts a fresh session — event fires again on next visit (sessionStorage scope is per-tab/session)

---

## Scenario 6: Multiple URL patterns matching same page

**Setup additions:**
- Configure a SECOND custom event:
  - `event_trigger` = `url`
  - `element_ref` = `/thank-you/?form=contact*`
  - `event_name` = `Contact` (or platform equivalent)

**Action:** Fresh session. Navigate to `/thank-you/?form=contact`.

**Asserts:**
- BOTH events fire — Lead (matches `/thank-you*`) AND Contact (matches `/thank-you/?form=contact*`)
- Two distinct events visible in network requests, two CAPI rows in event log
- Each has its own `event_id`

---

## Scenario 7: Cross-platform consistency

**Setup additions:**
- Same custom event configured for Google, TikTok, Pinterest, Microsoft (with platform-appropriate event names — e.g. `generate_lead` for Google)

**Action:** Fresh session. Navigate to `/thank-you/`.

**Asserts:**
- All 5 platforms fire (browser + CAPI per their respective send_client/send_server settings)
- Each platform's payload uses correct event name
- All sent within 5s of pageload

---

## Known gaps

- Multiple-session test (open two tabs simultaneously): not covered. Single-session is the priority.
- SPA-style navigation (no real pageload): out of scope for Phase 1.
- Page picker UX is verified via the `centralised-conversion-builder` flow — this flow tests runtime behaviour, not creation UI.
