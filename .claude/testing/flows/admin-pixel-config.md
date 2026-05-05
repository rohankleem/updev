# Flow: Admin — pixel configuration

**Status:** Draft
**Last run:** —
**Covers:** Each platform's settings page in wp-admin saves pixel ID + CAPI token, persists across reload, surfaces correct value back to the form.

## Setup

- WP admin session active (logged in as admin)
- Plugin active
- Starting URL: `https://updev.local.site/wp-admin/admin.php?page=unipixel`

---

## Scenario 1: General settings page loads

**Action:** Navigate to UniPixel main settings page.

**Asserts:**
- Page loads (200, no PHP errors visible)
- Navigation to each platform sub-page is present

---

## Scenario 2: Meta settings — save pixel ID

**Action:**
1. Navigate to Meta events page.
2. Set Pixel ID = `1234567890123456` (test value).
3. Set CAPI access token = `EAATEST_token_string` (test value).
4. Save.

**Asserts:**
- Save success message shown
- Form reloads with same values (no data loss)
- Frontend page (`/`) source contains `1234567890123456` in fbq init script (confirms saved value is being used)

**Captures:**
- DB option value (via WP CLI or inspection) → `expected/scenario-2-meta-options.json`

---

## Scenario 3: Repeat for each platform

For Google, TikTok, Pinterest, Microsoft, repeat Scenario 2 pattern with platform-appropriate test IDs:

- Google: GA4 Measurement ID like `G-XXXXXXXXXX`, MP API secret
- TikTok: Pixel ID like `CXXXXXXXXXXXXXXXXX`, CAPI access token
- Pinterest: Tag ID, CAPI access token
- Microsoft: UET tag ID, optional CAPI

**Asserts (per platform):**
- Save success
- Form retains values on reload
- Frontend shows value embedded in tracker init script

---

## Scenario 4: Disable platform — frontend stops loading it

**Action:** Toggle Meta off in admin. Save. Visit homepage (with consent granted).

**Asserts:**
- No `fbq` defined on frontend
- No `graph.facebook.com/tr` requests fire on PageView
- Other platforms still fire normally

---

## Scenario 5: Invalid input handling

**Action:** Enter clearly-invalid pixel ID (e.g. `<script>alert(1)</script>` or empty string).

**Asserts:**
- Form rejects OR sanitises (no XSS reflected)
- If empty: platform treats as disabled (no init script on frontend)
- If invalid format: error message shown OR value sanitised to safe state

---

## Scenario 6: Google G-001 mutex enforced inline

Google permits client-side OR server-side per non-Purchase event, never both. Purchase is the exception (both allowed for dedup). The admin UI must enforce this rule when the user toggles the per-event flags. See `domain-knowledge/platform-discoveries.md` § G-001 for the platform reasoning.

**Action:**
1. Navigate to the Google events page.
2. For ViewContent: tick `send_client = 1`. Save. Confirm save succeeds.
3. With `send_client` already on, tick `send_server = 1`. Attempt to save.

**Asserts:**
- Step 2 saves cleanly
- Step 3: the UI prevents both being on simultaneously. Either:
  - Saving the form refuses with an error message that names G-001 (or equivalent: "Google requires client OR server, not both, for non-Purchase events"), OR
  - The act of ticking `send_server = 1` automatically un-ticks `send_client = 1` client-side before the form submits, OR
  - Saving succeeds but the server-side handler silently coerces one of the flags off and surfaces a notice
- Capture which of the three behaviours the plugin actually implements (recorded for the contract; the test asserts the behaviour matches whatever was captured on the first blessed run)

**Action (Purchase exception):**
1. Navigate to Google events page.
2. For Purchase: set `send_client = 1, send_server = 1`. Save.

**Asserts:**
- Save succeeds without UI error
- Both flags persist after reload
- This is the deliberate Purchase exception — both must be allowed for dedup

**Captures:**
- The DB row for Google ViewContent after step 3 of the first action sequence → `expected/scenario-6-g001-viewcontent-row.json`
- The DB row for Google Purchase after the Purchase action → `expected/scenario-6-g001-purchase-row.json`

---

## Known gaps

- Each platform's exact admin URL slug needs confirming on first run (`page=unipixel-meta-events` or similar).
- Field names for capture vary per platform — capture by visible label rather than name attribute on first run, then refine.
- This flow is mostly browser-side; DB option-value capture needs WP CLI access or an admin endpoint that exposes the saved values.
