# Token-acquisition UX

**Status:** Complete (shipped 2026-05-12 to 2026-05-13). 14-day log-response grace period **removed 2026-05-17** before v2.7.0 release; see Phase 5 entry below.
**Backlog refs:** #34 in `release-log.md` (parent, now Done). Subsumes #1, #8, #30 (all Done-by-subsumption).
**Origin:** Moisés (nuespacios.com) support feedback, 2026-05-12.

---

## Why this matters

Moisés' support message captured a universal pattern. He wrote in asking *"how can I test if server-side tracking is working?"* with two underlying confusions: (1) Meta forced him through an app-creation flow to get a token and he wasn't sure he did it right, and (2) once pasted, he had no way to verify it was actually working.

Stepping back: getting from *"I installed UniPixel"* to *"I have a working access token pasted in"* is the single biggest install-to-active drop-off point. Every platform has the problem in a different shape. Platform UIs change underneath us (Meta's app-creation flow has shifted twice in 2 years; our in-plugin help still describes legacy paths). The verification flow today is forensic: navigate to logs, toggle a per-event setting, trigger an event, read HTTP codes, cross-check the platform's own UI. Most users don't get that far.

The strategic insight that the rest of this doc builds on: **verification has to be a button and a glance, not a forensic exercise.**

---

## Target experience (what good looks like)

For each platform's setup page in UniPixel admin:

1. **Status indicator** at the top of the page (header strip), three states:
   - *Not started* (grey, "Start setup" CTA opens the wizard)
   - *Pasted, unverified* (amber, "Test connection" CTA)
   - *Connected* (green, last server event timestamp on hover)

2. **Start setup wizard** (modal or inline accordion, form pending): walks the user through getting the platform's credentials, with reassurance copy (*"ignore Meta's Advantage+ prompts..."*, *"you don't need a Conversions API Gateway, UniPixel is the integration"*), direction-correct steps with deep links into the platform UI, ending at the Test Connection step.

3. **Test Connection button** next to the credential fields: validates the token against the platform's API, confirms the resource access (Pixel ID matches, dataset access, etc.), returns a plain-English result.

4. **Stored Event Logs** with plain-English error labels at the row level (401/403 = "Token problem", 400 = "Data problem", 200 = "Sent OK").

5. **Home dashboard badge** per platform: green/amber/red summary of the same state as the setup-page indicator.

The wizard handles the **client-side vs server-side split** explicitly. Two named journeys, each with its own *"what you'll achieve"* outcome. Pixel ID / Measurement ID is the client-side journey; access token is the server-side journey.

---

## Implementation plan

Meta first as the learning beat. Once stable, propagate the pattern to Google, then TikTok, Pinterest, Microsoft. Per-platform copy is bespoke; the structural pattern is shared.

The five concrete moves from the design conversation (Test Connection button, status badges, log default flip, help-icon refresh, plain-English error labels) are integrated across the phases below rather than treated as separate workstreams.

### Phase 1: Meta Test Connection (first slice) — SHIPPED 2026-05-12
- [x] Add "Test Connection" button next to the access token field on the Meta setup admin page
- [x] New PHP endpoint that calls Meta's Graph API: `debug_token` for token validity + scopes, then Pixel access check for the pasted Pixel ID
- [x] Inline result UI states: idle, validating, connected (green with Pixel ID echoed back), rejected (red with plain-English reason)
- [x] Plain-English error labels for: invalid token, expired token, missing `ads_management` scope, no Pixel access, wrong Pixel ID, network failure
- [x] Empty-token state surfaces as a Test Connection result (subsumes #8: no more silent fail when server-side is ON with an empty token)

**Files shipped:**
- NEW `admin/handlers/handler-meta-test-connection.php` (action `unipixel_meta_test_connection`, two-call Graph API check)
- NEW `admin/js/meta-test-connection.js` (visibility toggle on `#access_token` + `#pixel_id` input; click handler; inline alert render)
- MODIFIED `admin/page-meta-setup.php` (button row + result container inside `#serverside-fields`)
- MODIFIED `admin/admin.php` (`require_once` for the new handler; `wp_enqueue_script` for the new JS)

**Graph API version used:** `v18.0`. Existing `send-server-event.php` still uses `v14.0`. Cleanup of that older usage is a separate small task.

**Reused infrastructure:** existing `unipixel_ajax_nonce` nonce, existing `unipixel_ajax_obj` localised object (`ajaxurl` + `nonce`), existing `wp_remote_get` HTTP pattern, existing Bootstrap alert classes for the result UI.

**Ready for testing on `https://updev.local.site`.** Browser test flow: empty-state hides the button; input-driven visibility toggles it on/off; bad token returns red plain-English error; valid token + valid Pixel ID returns green with the Pixel name echoed back.

### Phase 2: Meta status indicator + home dashboard badge — SHIPPED 2026-05-12
- [x] Three-state header strip on the Meta setup admin page
- [x] *"Connected"* criteria settled (revised from initial proposal): credentials saved AND ( recent successful Test Connection click OR ≥1 successful server event in last 24h ). Hybrid definition so the strip flips green immediately after a Test Connection pass, not after waiting for events to flow.
- [x] Home dashboard badge per platform (Meta, TikTok, Google, Pinterest cards), pulling from the same helper, with last-verified timestamp on hover via the `title` attribute

**Files shipped:**
- MODIFIED `admin/handlers/handler-meta-test-connection.php` (writes `unipixel_test_connection_timestamps[1] = time()` on successful test)
- MODIFIED `functions/unipixel-functions.php` (new `unipixel_get_platform_connection_state($platform_id)` helper, platform-agnostic, reusable for Phases 7 and 8)
- MODIFIED `admin/page-meta-setup.php` (status alert strip inserted between page intro and form, 3 visual variants)
- MODIFIED `admin/page-home.php` (status badge inserted inside each of the 4 platform cards' configured variants)

**Known limitation 1 (token swap):** if a user changes the token to an invalid one after a successful Test Connection, the strip shows stale green until they re-click Test Connection. Acceptable for v1. Future enhancement: store a hash of the creds alongside the timestamp so cred changes invalidate the recorded test pass.

**Known limitation 2 (event-log path needs logging on):** the "≥1 successful event in 24h" half of the Connected check only fires if *Log Server-side Response* is ON for the events generating those rows. Phase 5's 14-day default-on resolves this for new installs.

**Small UX follow-up (not blocking):** the setup-page strip is server-rendered, so after a successful Test Connection click the green strip only appears after a page refresh. The button's inline alert provides immediate feedback. A small JS enhancement (swap strip classes + text on AJAX success) would make the strip live-update.

### Phase 3: Meta setup wizard ("Start setup" popup) — SHIPPED 2026-05-12
- [x] Built the wizard as a Bootstrap `modal-lg` overlay component (`#meta-setup-wizard-modal`)
- [x] Implemented the 7-step wizard spine (What you'll achieve / Prerequisites / What to ignore / Get your credentials with two deep links / Paste back / Test Connection / Done)
- [x] *"Looks different in your dashboard? Tell us"* link in every step. Reuses the existing `#unipixelFeedbackModal` (no new endpoint), pre-fills the description with the current step number and title, sets feedback type to Issue.
- [x] Per-platform copy for Meta authored under writing-style.md voice rules: no em dashes, no time estimates, second-person, "What to ignore" names the specific Meta upsells (Advantage+, Conversions API Gateway, audience templates, Lookalikes, Datasets beyond Pixel)
- [x] Start Setup button visible inside the not_started strip; Re-walk setup guide link visible inside the pasted_unverified and connected strips
- [x] Step 5 syncs inputs from the page form on modal open and mirrors back on save; Save fires the existing `unipixel_update_platform` AJAX
- [x] Step 6 fires the existing `unipixel_meta_test_connection` AJAX with the wizard's input values
- [x] Step 7 Close button reloads the page so the strip reflects the fresh Test Connection timestamp

**Files shipped:**
- NEW `admin/js/meta-setup-wizard.js` (modal navigation, input sync, embedded save + test connection, "Looks different?" wiring)
- MODIFIED `admin/page-meta-setup.php` (Start Setup button + Re-walk link in the status strip variants + wizard modal markup)
- MODIFIED `admin/admin.php` (`wp_enqueue_script` for the wizard JS, depending on jquery + bootstrap_bundle_js + unipixel-ajax-platform-settings)

**Verified end-to-end in browser (Rohan Personal Chrome Blue):** opened the wizard from the Re-walk link, walked steps 1-7, Test Connection at step 6 returned green and auto-advanced, Close at step 7 reloaded the page with the strip showing fresh "Verified X seconds ago", "Looks different?" link from step 1 correctly opened the existing feedback modal with description pre-filled as `Meta wizard, step 1 (What you'll achieve):`.

### Phase 4: Plain-English error labels in Stored Event Logs — SHIPPED 2026-05-12
- [x] Map HTTP codes (and common keyword shapes) to actionable plain-English badges at the log row level
- [x] Raw response stays available via Bootstrap popover (hover/focus) on the badge

**Mapping shipped:**
- HTTP 2xx, "Successful" keyword → green **"Sent OK"**
- HTTP 400, "bad request", "malformed" → red **"Data problem"**
- HTTP 401/403, "unauthorized/unauthorised/forbidden/invalid token" → red **"Token problem"**
- HTTP 429, "too many requests/rate limit" → amber **"Rate limited"**
- HTTP 5xx, "server error" → red **"Platform server error"**
- HTTP 4xx (other) → red **"Request problem"**
- "curl/timeout/could not resolve/connection/wp_error" → red **"Could not reach platform"**
- Empty response OR placeholder "Response logging turned off..." → grey muted **"Not logged"**
- `method = 'client'` rows → blue **"Client-side, no response"** (expected behaviour, not an error)
- Anything else → amber **"Other"**

**Files shipped:**
- MODIFIED `functions/unipixel-functions.php` (new `unipixel_classify_event_log_response($response, $method)` helper, returns `{label, level, bootstrap_class, raw}`)
- MODIFIED `admin/page-event-logs.php` (Response column now renders a badge styled by the helper's class + Bootstrap popover with the raw response detail; existing popover init handles the new triggers)

**Verified in browser:** Stored Event Logs page now renders three distinct visual states across the rows (green Sent OK for the two Lead server events that returned 200, grey Not logged for ViewContent server rows where logging was off, blue Client-side no response for every client row). Hover on a badge surfaces the original response detail.

### Phase 5: "Log Server-side Response" 14-day default + visibility — REMOVED 2026-05-17 (was shipped 2026-05-12, removed before v2.7.0 release)

**Reason for removal.** The whole approach was a judgment error. WP-Cron is unreliable across hosts (low-traffic sites only fire cron on page loads, some hosts disable WP-Cron, system-cron isn't always configured), so the 14-day auto-off was going to silently fail on a meaningful subset of installs. More importantly, the underlying problem (new users not seeing server-side response data) is already solved by the Test Connection button + plain-English error badges shipped in the same release. Adding scheduled automation on top was redundant complexity, and it changed user-configured settings without the user knowing.

Per Rohan: *"It should be solved by UX, not through a cron job and without a user knowing."*

**Files reverted (2026-05-17):**
- `functions/unipixel-functions.php` — deleted `unipixel_start_log_response_grace_period()`, `unipixel_end_log_response_grace_period_callback()`, `unipixel_get_log_response_grace_status()`, and the `add_action('unipixel_end_log_response_grace_period', ...)` registration.
- `admin/handlers/handler-platform-settings.php` — removed the trigger block on empty→non-empty token transition.
- `admin/page-event-logs.php` — removed the intro info alert.
- `admin/page-meta-setup.php`, `admin/page-google-setup.php`, `admin/page-tiktok-setup.php`, `admin/page-pinterest-setup.php`, `admin/page-microsoft-setup.php` — removed conditional "X days remaining" alerts inside each `#serverside-well`.
- `unipixel.php` `unipixel_activate()` — added cleanup that clears any orphan scheduled `unipixel_end_log_response_grace_period` events for platform IDs 1-5 and deletes the `unipixel_log_response_grace_started_at` option. So any install that ever triggered the grace flow (Rohan's local dev included, if/when WP-Cron processed the trigger) gets cleaned on next plugin activation.
- All 9 touched files lint clean.

**Lesson saved as feedback memory** (`feedback_no_cron_dependent_ux_automation.md`): don't introduce WP-Cron-dependent automation for UX problems; flag cron dependency as a design objection before writing code; propose non-scheduled alternatives first.

**Original implementation notes preserved below for context:**

### Phase 5 (original spec, now removed)
- [x] Implement default ON for the first 14 days after a platform token is added, then auto-OFF
- [x] Add user-facing note explaining this behaviour so users aren't confused when logs stop appearing (Stored Event Logs page intro + Meta setup page near the logging context)
- [x] Confirm the wizard's Test Connection step relies on logging being available, so first-time users immediately see results (confirmed: the Test Connection AJAX endpoint validates against Meta's Graph API directly and doesn't depend on `send_server_log_response` being on for any event)

**Implementation approach (database-level, not pipeline-level):** rather than modifying the gate in every caller across `woocomm-hook-handling/*.php` and `trackers/*-ajax-listener-send-server.php` (which would touch ~11 files in the event-firing pipeline and risk regressions), the grace period bulk-updates `send_server_log_response = 1` on every event row for the platform at the moment the token is first added. A scheduled WP-Cron event 14 days out bulk-resets the flag back to 0. The existing event-firing pipeline is untouched; it just sees the flag values it always saw.

**Idempotency:** triggers only on a first-time empty→non-empty token transition per platform. Subsequent token changes do not re-trigger, so user-customised per-event toggles aren't overwritten.

**Files shipped:**
- MODIFIED `functions/unipixel-functions.php`:
  - `unipixel_start_log_response_grace_period($platform_id)` — records timestamp, bulk-updates both event tables, schedules cron
  - `unipixel_end_log_response_grace_period_callback($platform_id)` — cron callback, bulk-resets to 0 (with defensive re-check that 14 days have actually passed)
  - `unipixel_get_log_response_grace_status($platform_id)` — read helper for UI notes, returns `{active, started_at, ends_at, days_remaining}`
  - `add_action('unipixel_end_log_response_grace_period', ...)` registered at file-load time so WP-Cron can fire it
- MODIFIED `admin/handlers/handler-platform-settings.php` (calls `unipixel_start_log_response_grace_period` when token transitions empty → non-empty)
- MODIFIED `admin/page-event-logs.php` (intro info alert explaining the 14-day behaviour and the "Not logged" badge)
- MODIFIED `admin/page-meta-setup.php` (conditional info alert inside `#serverside-well` showing "X days remaining" when the grace period is active; hidden otherwise)

**Verified in browser:** Stored Event Logs page now shows the explanatory intro alert. Meta setup page renders cleanly (grace note hidden because this install's token predates Phase 5). All four touched PHP files lint clean.

**Manual verification path for the grace period itself (Rohan):** clear the Meta access token, save, paste it back, save again. The empty→non-empty transition triggers the bulk-update + scheduled cron, and the Meta setup page should then display the "X days remaining" info alert. Equally applicable to Pinterest/TikTok/Google/Microsoft (the helper is platform-agnostic), though their setup pages don't yet render the alert — that'll come during Phase 7/8 propagation.

### Phase 6: Refresh in-plugin help-icon copy (wizard launcher) — SHIPPED 2026-05-12
- [x] Help icons next to credential fields refreshed to a one-line anchor + "Open setup guide" link
- [x] Old stale legacy 6-step System User instructions in `Meta_AccessToken` removed; old external-docs link in `Meta_PixelId` replaced

**New help-icon content (Meta):**
- `Meta_PixelId`: *"Your unique identifier for your Meta Pixel. Found in Meta Business Settings under Data Sources. [Open setup guide]"*
- `Meta_AccessToken`: *"A long-lived token for Meta's Conversions API (server-side tracking). Created via a System User in Meta Business Settings with the `ads_management` permission. [Open setup guide]"*

**Wizard launcher mechanism:** the "Open setup guide" link uses `href="#meta-setup-wizard"`. A document-level jQuery delegation handler in `admin/js/meta-setup-wizard.js` intercepts clicks on `a[href="#meta-setup-wizard"]`, calls `e.preventDefault()`, and opens the wizard modal via `bootstrap.Modal.getOrCreateInstance(...).show()`. Using an href-based selector instead of `data-bs-toggle="modal"` survives Bootstrap's popover sanitizer (which strips `data-bs-*` attributes from inner links by default).

**Files shipped:**
- MODIFIED `functions/unipixel-functions.php` (replaced `Meta_PixelId` and `Meta_AccessToken` help-text entries; trimmed to one-liner + wizard link)
- MODIFIED `admin/js/meta-setup-wizard.js` (added document-level `$(document).on('click', 'a[href="#meta-setup-wizard"]', ...)` delegation that opens the modal)

**Verified in browser:** popover renders new content; clicking the "Open setup guide" link opens the wizard modal (verified by injecting the new handler manually since cached `meta-setup-wizard.js` was still the pre-Phase-6 version under the same UNIPIXEL_VERSION query string). Real users will get this on the next version bump that busts the JS cache, or after a hard refresh.

### Phase 7: Propagate to Google — SHIPPED 2026-05-13
- [x] Google's structure handled: Measurement ID (in `pixel_id` column) + API Secret (in `access_token` column) + optional GTM Container ID (in `additional_id`). No "app" concept like Meta.
- [x] Author Google wizard copy with primary-source verification (WebSearched + WebFetched Meta-style Google docs to ground the wizard, then caught a wrong-URL bug at test time)
- [x] G-001 mutex acknowledged in wizard step 3 (Google deduplicates only Purchase events)
- [x] **Honest design limitation captured:** Google's debug endpoint does NOT validate api_secret or measurement_id values. Test Connection can only confirm credentials are well-formed and event payload is accepted. The success message says so explicitly: *"Validation passed. Test event accepted by Google's debug endpoint. Note: Google does not validate the API Secret value itself, so confirm real events are landing by checking GA4 Realtime."*

**Files shipped:**
- NEW `admin/handlers/handler-google-test-connection.php` (action `unipixel_google_test_connection`, validates measurement_id format via regex `^G-[A-Z0-9]+$`, then POSTs sample event to `https://www.google-analytics.com/debug/mp/collect`, parses `validationMessages` array, writes timestamp to `unipixel_test_connection_timestamps[4]` on success)
- NEW `admin/js/google-test-connection.js` (button visibility on `#pixel_id` + `#access_token` inputs, click handler, inline result alert)
- NEW `admin/js/google-setup-wizard.js` (7-step modal navigation, document-level delegation for `a[href="#google-setup-wizard"]`, embedded Save via existing `unipixel_update_platform`, embedded Test Connection via new action, "Looks different?" pre-fill with `Google server-side wizard, step N (title):`)
- MODIFIED `admin/page-google-setup.php` (status strip with 3 variants using existing helper, Start Setup button in not_started, Re-walk link in pasted_unverified + connected, Test Connection button + result row in `#serverside-fields`, conditional 14-day grace banner, full wizard modal markup)
- MODIFIED `admin/admin.php` (`require_once` for new handler, 2 new `wp_enqueue_script` calls)
- MODIFIED `functions/unipixel-functions.php` (`Google_MeasurementId` and `Google_ApiSecret` help-icon copy refreshed to one-liner + `[Open setup guide]` link with `href="#google-setup-wizard"`)

**Bug caught at test time:** my initial handler used `https://www.google-analytics.com/_debug_/mp/collect` (with underscores) which returns 404. The actual Google endpoint is `/debug/mp/collect` (no underscores). The WebFetch summary I read during research had added the underscores. Lesson: when WebFetching docs for API endpoints, always verify the actual URL with a direct curl rather than trusting the AI summary. Fixed inline during browser test.

**Verified end-to-end in browser:** Re-walk link opens Google wizard. Step 6 Test Connection POSTs to Google's debug endpoint, receives `{"validationMessages": []}` for valid payload, renders green success with honest caveat. Page reload shows the strip flipped to "Server-side connected. Verified X seconds ago." All platform-agnostic helpers from earlier phases (`unipixel_get_platform_connection_state(4)`, `unipixel_get_log_response_grace_status(4)`) work for Google without modification.

### Phase 8: Propagate to TikTok, Pinterest, Microsoft — SHIPPED 2026-05-13
- [x] **TikTok wizard + Test Connection — SHIPPED 2026-05-13.** Three new files (handler, test-connection JS, wizard JS), three file modifications (page-tiktok-setup.php strip+button+wizard+grace banner, admin.php handler+JS enqueues, unipixel-functions.php help-icon refresh). Endpoint `https://business-api.tiktok.com/open_api/v1.3/event/track/` verified by direct curl (returns HTTP 401 for bogus token with empty body, returns `{code:0, message:"OK"}` for valid). Format checks: Pixel ID regex `^[A-Z0-9]{18,30}$` (TikTok IDs ~20 uppercase alphanumeric like C8C3JPS5R0L0CKHEJ8K0), Access Token regex `^[A-Za-z0-9]{30,80}$` (pure alphanumeric, no underscores). Sends test event with auto-generated `test_event_code: UP_TEST_<random>` so the event lands in TikTok Events Manager → Test Events tab (doesn't pollute production reports). New helper `unipixel_diagnose_tiktok_error()` pattern-matches TikTok soft errors (code != 0) on message keywords: "access token" → invalid_token, "permission/unauthor" → token_missing_permission, "pixel + not exist/invalid/not found" → no_pixel_access, "rate" → rate_limited, fallback passes TikTok's raw code+message through. Wizard step 3 includes the TT-003 quirk note about Reserved Event Names being silently rolled into Standard events. Verified end-to-end: bogus token with underscores correctly format-rejected; right-format-but-bogus token correctly translated to actionable "Regenerate it in TikTok Events Manager → Pixels → your Pixel → Settings → Access Token" message.
- [x] **Pinterest wizard + Test Connection — SHIPPED 2026-05-13.** Three new files, three modifications. Pinterest requires THREE credentials: Tag ID (in `pixel_id` column), Ad Account ID (in `additional_id`), and Conversion Access Token (in `access_token`). Validation strategy is two-call: `GET https://api.pinterest.com/v5/user_account` with `Authorization: Bearer <token>` validates the token (401 = invalid, 200 = valid); then `GET https://api.pinterest.com/v5/ad_accounts/{ad_account_id}` validates the ad account access (404 = wrong ID or no access, 403 = no permission, 200 = OK with account name in body). Both endpoints verified by direct curl. Format checks: Tag ID numeric 10-20 digits, Ad Account ID numeric 8-20 digits, Token regex `^(pina_)?[A-Za-z0-9_\-]{30,200}$` (accepts both legacy and newer `pina_` prefixed tokens). Wizard step 3 includes the PIN-001 quirk note about Pinterest only accepting 6 custom event-tier names (custom/lead/search/signup/view_category/watch_video). Success message echoes back the ad account name from Pinterest's response. Verified end-to-end: bogus values correctly diagnosed.
- [x] **Microsoft wizard + Test Connection — SHIPPED 2026-05-13.** Three new files, three modifications. Endpoint `https://capi.uet.microsoft.com/v1/{tag_id}/events` verified by direct curl (returns HTTP 401 with structured JSON body `{"error":{"code":"Unauthorized","message":"..."},"traceId":"..."}` for bogus token). Microsoft's structured error response is much better than TikTok/Pinterest's empty bodies — we can extract `error.code` + `error.message` and pass through. Format checks: UET Tag ID numeric 6-12 digits (~7-9 typical), Access Token alphanumeric/underscore/hyphen/dot 30-500 chars. HTTP-status-driven dispatch: 401 → invalid_token, 403 → token_missing_permission, 404 → no_tag_access, 429 → rate_limited, 5xx → platform_server_error, other 4xx → invalid_request with Microsoft's error.code + error.message. Wizard step 2 explicitly acknowledges the CAPI gating: "Microsoft CAPI is gated. Most accounts don't get a CAPI token automatically. You may need to request access from your Microsoft Advertising account manager. If you only have a UET Tag (no CAPI token), you can still do client-side tracking; just skip this wizard and add only the UET Tag ID." Verified end-to-end: bogus token → HTTP 401 → actionable message with Microsoft's raw error text included for transparency.

**Phase 8 in summary:** All four other platforms now have parity with Meta + Google for Test Connection, status indicator strip, wizard, grace period UI, and help-icon refresh. Each Test Connection has platform-specific format checks and platform-specific diagnostic feedback parsing. All endpoints verified by direct curl before code-writing (no more flip-flopping on URLs/shapes). Platform-agnostic helpers from earlier phases reused throughout for `platform_id` 2, 3, 5.

---

## Design decisions

### Open (need answers before relevant phase starts)

*All Phase 1 design decisions resolved. Future phases may surface new decisions; record them here.*

**Phase 3 (wizard) decisions, resolved 2026-05-12:**
- *Test Connection embed at step 6:* wizard renders its own copy of the token + Pixel ID inputs + Test Connection button. Self-contained, cleaner UX than handing off back to the page.
- *"Looks different in your dashboard? Tell us" target:* triggers the existing *"Something Not Working?"* feedback modal that already lives on every UniPixel admin page (rendered by `unipixel_render_feedback_modal()`). Reuses the existing capture pipeline. Wizard step context should be pre-filled where possible.
- *Auto-open vs button-only:* button-only. No auto-popping when a not-started user lands on the page. Less aggressive.
- *Re-walk from non-Not-Started state:* add a quiet *"Re-walk setup guide"* link visible in Pasted unverified and Connected states, so returning users can re-open the wizard without having to clear credentials first.

### Resolved

- **2026-05-12.** Meta first as learning beat. Pattern propagates to other platforms once stable.
- **2026-05-12.** Wizard steps stay direction-correct, not click-by-click. Survives platform UI churn better than instructions referencing specific buttons.
- **2026-05-12.** Each wizard step gets a *"Looks different in your dashboard? Tell us"* feedback link.
- **2026-05-12.** No time estimates in any wizard or help copy (per writing-style.md § 11).
- **2026-05-12.** Client-side and server-side credentials are named as two separate journeys in the wizard (Pixel ID journey vs. access token journey). Different *"what you'll achieve"* outcomes.
- **2026-05-12.** *"What to ignore"* / reassurance copy is high-leverage and unique to UniPixel's cross-platform position. Treated as content, not throwaway.
- **2026-05-12.** *Log Server-side Response* default behaviour: ON by default for the first 14 days after a platform token is added, then automatically OFF. The 14-day auto-off behaviour must be surfaced to the user in-product so they understand why logs stop appearing. Likely placement: a hint on the Stored Event Logs page intro and a note on the platform setup page near the logging context. Exact copy and placement designed during Phase 5.
- **2026-05-12.** Buttons appear only when relevant to the current state. *Test Connection* appears only when a token has been entered (Pasted unverified, Connected). *Send Test Event* appears only when Connected. *Start Setup* appears only when Not Started. No dead or disabled buttons, no actions surfaced before they can work.
- **2026-05-12.** Wizard form factor: **modal popup overlay**. Inline-accordion mode for returning users can be revisited after Meta ships, but v1 is modal only.
- **2026-05-12.** *Send Test Event* is a **separate button** from *Test Connection*. Test Connection is read-only validation (safe to spam). Send Test Event uses #30's `test_event_code` to fire a real-looking event tagged for Meta's Test Events tab. Visibility follows the button-affordance rule: Test Connection appears once a token is entered, Send Test Event appears only when Connected.
- **2026-05-12.** *"Connected"* criterion for the Test Connection button: token authenticates against Meta's Graph API **AND** has access to the pasted Pixel ID. Two API calls (`debug_token` plus a Pixel access check). Catches both the invalid-token case and the pasted-the-wrong-token case. The longer-running *"has fired ≥1 successful event in 24h"* signal is the home dashboard badge's job (Phase 2), not the Test Connection button's.
- **2026-05-12.** Status indicator: **3 states** (Not started / Pasted unverified / Connected). Failures surface inline in the Test Connection result, not as a persistent fourth Error state.

---

## What rides along (existing backlog items)

- **#8 PHP validation for empty token + serverside ON.** Silent-fail today. Subsumed by Phase 1: Test Connection's empty-token state is a clear *"no token entered yet"* or *"token required for server-side"* result.
- **#30 Meta `test_event_code` field.** Sanctioned Meta way to verify. Lets events show in Meta's Test Events tab labelled as test, without polluting production data. Ships in Phase 3 (wizard "Send test event" step) or as a standalone button alongside Phase 1's Test Connection.
- **#1 Setup wizard / onboarding flow.** This initiative is substantially what #1 was originally scoped as. When the token-acquisition wizard ships across all 5 platforms, #1 is effectively done.

---

## Maintenance plan

- Every wizard step ships with a *"Looks different in your dashboard? Tell us"* link.
- One-click feedback captures the user's current URL plus their plugin install ID into a small admin queue.
- That queue is what tells us when a platform has moved a button. Lower friction than waiting for a support email.
- Periodic check: walk through each platform's wizard end-to-end on a clean account. Catch drift before users do.

---

## Reflect back to permanent docs when done

When this initiative completes (or at major phase boundaries), retained knowledge to herd back:

- **Per-platform validation patterns** (Meta `debug_token` + Pixel access; Google Tag Manager / Ads API patterns; TikTok identity API; etc.) → `app-knowledge/app-knowledge.md`
- **Wizard component structure** (steps, deep-link pattern, stale-step feedback link, *"Looks different?"* queue) → `app-knowledge/app-knowledge.md`
- **"What to ignore" copy patterns per platform** → `domain-knowledge/platform-discoveries.md` (or a new dedicated file if the pattern grows)
- **"Connected" state definition logic** → `app-knowledge/app-knowledge.md`

---

## Session log

- **2026-05-12.** Initiative defined off the back of Moisés (nuespacios.com) support feedback. Replied to Moisés with verification flow (Stored Event Logs filtered by Platform=Meta + Method=server, looking for Code 200; plus Meta Events Manager cross-check). Mapped the five concrete moves (Test Connection button, status badges, log default flip, help-icon refresh, plain-English error labels). Rohan proposed the richer wizard + status-indicator vision integrating all five. Meta-first agreed. Doc created.
- **2026-05-12 (Phase 1 implementation).** Built the Meta Test Connection slice. New `admin/handlers/handler-meta-test-connection.php` does Graph API v18.0 two-call validation (`debug_token` then `/{pixel_id}` with `fields=id,name`). New `admin/js/meta-test-connection.js` controls button visibility (only shown when both `#access_token` and `#pixel_id` have values) and renders the inline alert. `admin/page-meta-setup.php` got the button row inside `#serverside-fields`. `admin/admin.php` got the handler include and JS enqueue. All three PHP files lint clean.
- **2026-05-12 (Phase 1 verified).** Rohan tested on local dev: "meta one seems to work". Phase 1 done.
- **2026-05-12 (Phase 2 implementation).** Built the connection state machine + status indicators. New helper `unipixel_get_platform_connection_state($platform_id)` in `functions/unipixel-functions.php` returns `{state, last_test_at, last_event_at}`, used by both the setup-page strip and the home dashboard badges. State definition was revised mid-build after Rohan flagged that the pure data-driven definition would leave the strip amber after a successful Test Connection click. Switched to hybrid: Test Connection timestamp OR recent events. Phase 1 handler updated to write the timestamp on success via `unipixel_test_connection_timestamps` option. Status strip added to Meta setup page (3 visual variants). Home dashboard badges added to Meta, TikTok, Google, Pinterest cards. All four PHP files lint clean. Browser testing pending Rohan.
- **2026-05-12 (Phase 2 verified).** Browser-tested via Claude in Chrome MCP. Meta strip flipped amber → green after Test Connection + refresh. Home dashboard showed all three badge states simultaneously (Meta green, Google + Pinterest amber, TikTok no badge). Pinterest setup page correctly untouched. No console errors.
- **2026-05-12 (Phase 3 implementation + verified).** Built the Meta setup wizard. NEW `admin/js/meta-setup-wizard.js` handles 7-step navigation, input sync from page form, embedded Save (via `unipixel_update_platform`) at step 5, embedded Test Connection (via `unipixel_meta_test_connection`) at step 6, "Looks different?" wiring to the existing `#unipixelFeedbackModal` with step-context pre-fill. Modal markup added inside the UniPixelShell in `admin/page-meta-setup.php`. Start Setup button placed in the not_started strip; Re-walk setup guide link placed in the pasted_unverified and connected strips. Browser-tested end-to-end: walked all 7 steps including the embedded Test Connection success and page reload. "Looks different?" verified opening the existing feedback modal with `"Meta wizard, step 1 (What you'll achieve):"` pre-filled.
- **2026-05-12 (Phase 1-3 scope labelling clarified).** All user-facing labels relabelled as **server-side specifically** so client-side-only users aren't misled. Strip text: "Not set up." → "Server-side tracking not set up.", "Pasted, unverified." → "Server-side not yet verified.", "Connected." → "Server-side connected.". Buttons: "Start setup" → "Start server-side setup", "Re-walk setup guide" → "Re-walk server-side setup". Wizard modal title: "Meta Setup Walkthrough" → "Meta Server-Side Setup Walkthrough". Home dashboard badges across all 4 platform cards: "Connected" → "Server-side connected", "Pasted, unverified" → "Server-side unverified". Wizard "Looks different?" pre-fill text: "Meta wizard, step N" → "Meta server-side wizard, step N". The state-machine internals (`not_started`, `pasted_unverified`, `connected`) and the underlying definition are unchanged; this is purely a labelling pass so the scope is unambiguous to users. Verified in browser.
- **2026-05-12 (Phase 4 implementation + verified).** Built the plain-English response classifier. NEW helper `unipixel_classify_event_log_response($response, $method)` in `functions/unipixel-functions.php` maps raw response strings to badge tuples. Mapping covers HTTP code extraction (Code XXX, JSON-shaped code field, standalone 3-digit code) plus keyword fallback (Successful, unauthorized, invalid token, curl, timeout, etc.). Server rows with logging off get muted "Not logged" badge; client rows get blue "Client-side, no response". `admin/page-event-logs.php` Response column rewired to render the badge with a Bootstrap popover carrying the raw text. First browser test exposed a missed pattern (existing rows store the literal placeholder "Response logging turned off for this event" rather than empty string); fixed by adding that substring to the Not-logged matcher. Verified in browser: three clean visual states (green Sent OK / grey Not logged / blue Client-side, no response) replacing the previous raw text dump.
- **2026-05-12 (Phase 5 implementation + verified).** Built the 14-day Log Server-side Response grace period. Implementation chose the database-level path (bulk-update the flag at token-add time, scheduled cron to reset 14 days later) over modifying the gate in the ~11 event-firing pipeline files, to keep risk of regressions low. Three new functions in `functions/unipixel-functions.php`: `unipixel_start_log_response_grace_period`, `unipixel_end_log_response_grace_period_callback` (with `add_action` registration), `unipixel_get_log_response_grace_status`. Triggered from `handler-platform-settings.php` on first empty→non-empty token transition per platform (idempotent — won't re-trigger on subsequent token changes). User-facing info alert added to Stored Event Logs intro (always on) and to Meta setup page's Server-Side Tracking section (conditional on `unipixel_get_log_response_grace_status(1)['active']`). Verified in browser: Stored Event Logs alert renders; Meta setup page renders cleanly with grace alert correctly hidden (this install's token predates Phase 5). End-to-end grace-trigger test deferred for Rohan because verifying it would require destroying the working test token by clearing-and-re-adding.
- **2026-05-12 (Phase 6 implementation + verified).** Refreshed `Meta_PixelId` and `Meta_AccessToken` help-icon copy in `functions/unipixel-functions.php`. Old wall-of-text legacy 6-step System User instructions replaced with one-line description + `[Open setup guide]` link (`href="#meta-setup-wizard"`). The link is wired up via a new document-level click delegation handler at the top of `admin/js/meta-setup-wizard.js`. First attempt used `data-bs-toggle="modal"` on the inner link but Bootstrap's popover sanitizer strips those data attributes by default; switched to an href-anchor selector pattern that survives sanitization. Browser-verified end-to-end (Pixel ID popover shows new content; "Open setup guide" link opens the wizard modal). Real users get this automatically on the next version bump that busts the JS cache.
- **2026-05-13 (Phase 7 shipped + verified).** Propagated Test Connection + status strip + wizard to Google. Three new files (handler, test-connection JS, wizard JS). Four file modifications (page-google-setup.php, admin.php, unipixel-functions.php). All platform-agnostic helpers from Phases 1-6 reused unchanged for `platform_id=4`. Bug caught during browser test: my initial handler used wrong Google debug endpoint URL (`/_debug_/` with underscores from a WebFetch summary; real endpoint is `/debug/`); fixed inline, retested, green success. Test Connection success message includes honest caveat that Google does not validate API Secret values server-side. Wizard step 3 acknowledges G-001 mutex (Google deduplicates only Purchase events). Help-icon copy refreshed.
- **2026-05-13 (Phase 7 strengthened after Rohan flagged false-positive risk).** Initial Google Test Connection returned green even for a too-short bogus API Secret because Google's debug endpoint doesn't validate api_secret values. Rohan correctly pushed back. Enhanced the handler to a three-layer check: (1) format regex on API Secret `^[A-Za-z0-9_-]{15,40}$` catches obviously bogus values like `test123`, (2) debug endpoint payload structure validation (existing), (3) NEW: fires the same event to the production `/mp/collect` endpoint with `debug_mode: 1`, which makes the event land in **GA4 DebugView**. Success message now tells the user to look in GA4 → Admin → DebugView for `unipixel_test_connection` within 60 seconds — that's the definitive verification of whether the API Secret value is actually correct (Google won't tell us directly). Wizard step 6 copy rewritten to explain the layered approach and the DebugView verification path. Verified end-to-end: the saved test-placeholder API Secret on the dev install now correctly returns red `invalid_api_secret_format` with an actionable message, where it previously returned false-positive green.
- **2026-05-13 (Phase 8 shipped — all three remaining platforms in one push).** TikTok, Pinterest, and Microsoft all got their Test Connection + status strip + wizard + grace banner + help-icon refresh. Each platform researched primary-source first (search + direct curl to verify endpoint URLs and response shapes before writing code), to avoid the Google `/_debug_/` flip-flop. TikTok endpoint `https://business-api.tiktok.com/open_api/v1.3/event/track/` with Access-Token header (returns `{code:0,...}` for success, 401 for invalid). Pinterest two-call validation: `/v5/user_account` + `/v5/ad_accounts/{id}` (the only platform that needs THREE credentials: Tag ID, Ad Account ID, Token). Microsoft `https://capi.uet.microsoft.com/v1/{tag_id}/events` with structured JSON error body that we parse through cleanly. Phase 8 wizards each have platform-specific "what to ignore" content + platform-specific quirk notes (TT-003 Reserved Event Names for TikTok, PIN-001 6-custom-tier-names for Pinterest, CAPI gating disclosure for Microsoft). Nine new files (3 handlers, 3 test-connection JS, 3 wizard JS), nine file modifications (3 page-setup, 3 admin.php edits, 3 help-icon refreshes in unipixel-functions.php). All PHP lints clean. All three Test Connection AJAX endpoints verified end-to-end with bogus credentials returning correct actionable diagnostic messages.
- **2026-05-13 (Phase 1 / Meta strengthened with diagnostic feedback).** Following the Google strengthening, Rohan asked whether Meta could be similarly improved by parsing Meta's richer response signals. Substantial rewrite of `admin/handlers/handler-meta-test-connection.php`: (1) **Format checks before API call** — Pixel ID regex `^\d{14,17}$` (with cross-platform hint flagging that Google IDs start with `G-`, TikTok and Pinterest IDs look different), Access Token check for `EAA` prefix + ≥ 100 chars (also flags app access token format `app_id|app_secret` as wrong type for CAPI); (2) **Scope parsing from debug_token response** — checks `scopes` array for `ads_management`; if missing, returns `token_missing_scope` with specific regen instructions; (3) **Expiry parsing from `expires_at`** — if finite and within 7 days, appends a warning to the success message; if 0 (long-lived), no warning; (4) **New `unipixel_diagnose_meta_error()` helper function** translates Meta's error codes + subcodes into specific actionable messages: 190/460 "password changed", 190/463+467 "expired", 190/458 "app uninstalled", 190/459 "checkpoint required", 200/220 "missing permission", 100 "invalid parameter (likely wrong Pixel ID or no access)", 4/10/17 "rate limited", 230 "permissions disabled"; (5) **Pixel access call** maps code 100 specifically to actionable *"Open Business Settings, select your system user, click Add Assets, and grant access to this Pixel with at least the View partial access."* Verified against real saved token (returned full success with scopes parsed and `ads_management` confirmed present, `expires_at: 0` long-lived) and three deliberate failure modes (wrong Pixel ID format → `invalid_pixel_id_format` with cross-platform hint; right-format but bogus Pixel ID → `no_pixel_access` with actionable diagnostic; wrong token format → caught before hitting Meta's API).
- **2026-05-12 (Wizard Step 4 rewritten after Meta-flow verification).** WebSearched + WebFetched current Meta CAPI setup docs (Meta developer docs, Elevar, owntag) to verify the actual current token-generation flow. Confirmed: Meta auto-creates the app and system user as part of the Events Manager "Generate Access Token" flow when the user doesn't already have one. The manual System Users path still exists for agency setups but is no longer the recommended primary route. Rewrote wizard step 4 in `admin/page-meta-setup.php` to lead with the easy path (Events Manager → Pixel → Settings → Conversions API → Generate Access Token) as a numbered 4-step procedure, with deep link to Events Manager (`https://business.facebook.com/events_manager2/`). Added Developer-access permission caveat. Added a collapsible `<details>` disclosure with the manual System Users path for agency users. Earlier wizard content described only the legacy manual path which was stale guidance. Browser-verified: both paths render correctly, easy path prominent and manual path tucked under disclosure.
