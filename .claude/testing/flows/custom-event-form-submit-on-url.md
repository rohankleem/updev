# Flow: Custom event — Form submission on URL

**Status:** Draft (deferred — pending Phase 1 scope decision; see project open question 1)
**Last run:** —
**Covers:** Phase 1 (potentially) of [centralised-event-builder](../../projects/centralised-event-builder.md). Custom event with `form_submit_on_url` trigger fires when a form submits AND the page URL matches the pattern.

> **Note:** This flow only becomes Active if `form_submit_on_url` is included in Phase 1. Otherwise it remains Draft until plugin-specific form integration ships separately.

## Setup

**Required state delta from baseline:**
- Custom event configured for Meta (start point):
  - `event_trigger` = `form_submit_on_url`
  - `element_ref` = `/contact*` (URL pattern — fires on form submission while on `/contact*` URLs)
  - `event_name` = `Lead`
  - `send_client = 1`, `send_server = 1`, `send_server_log_response = 1`
- Consent: granted (full)
- Plugin console logging ON
- A page at `/contact/` exists with a working `<form>` that POSTs and redirects to `/thank-you/`
- A page at `/sample-page/` exists with no forms

---

## Scenario 1: Form submitted on matching URL fires event

**Action:** Navigate to `/contact/`. Fill form. Submit (any method — button click, Enter key, JS submit).

**Asserts:**
- Native `submit` event fired on the form
- Lead event request to Meta within 2s
- CAPI row in Stored Event Log
- (Detection method dependent) Validation-failed submissions don't fire — see Scenario 4

**Captures:**
- Meta Lead payload (browser) → `expected/scenario-1-meta-lead-browser.json`
- Meta Lead payload (CAPI) → `expected/scenario-1-meta-lead-capi.json`

---

## Scenario 2: Form submitted on non-matching URL does NOT fire

**Action:** Navigate to a page with a form on a URL that doesn't match (e.g. site search form on homepage, where pattern is `/contact*`). Submit it.

**Asserts:**
- No Lead event fires
- Native `submit` event still fires (we just don't track it)

---

## Scenario 3: User on matching URL but doesn't submit

**Action:** Navigate to `/contact/`. Wait. Don't submit. Navigate away.

**Asserts:**
- No Lead event fires (URL alone doesn't trigger; submit alone doesn't trigger)

---

## Scenario 4: Submit attempt that fails validation

**Action:** Navigate to `/contact/`. Submit form with invalid data (empty required field, invalid email).

**Asserts:**
- Behaviour depends on detection method (see project doc Phase 1):
  - **Native submit only**: event fires anyway (counts attempts, not successes — known limitation)
  - **Submit + nav heuristic**: event does NOT fire (page didn't unload because validation blocked)
- Document which detection method was implemented and what the actual behaviour is

---

## Scenario 5: Browser/CAPI dedup

**Asserts:**
- `event_id` matches between browser and CAPI

---

## Scenario 6: Fire-once-per-session

**Action:** After Scenario 1, reload `/contact/` and submit again.

**Asserts:**
- Default ON: second submission does NOT fire (same conversion in same session)
- Test the override toggle if exposed in admin

---

## Known gaps

- AJAX form support is out of scope for `form_submit_on_url` — handled by plugin-specific integration in a future feature
- Hand-built JS forms that prevent default and submit via fetch: behaviour depends on detection method, document on first run
- Multiple forms on the same matching URL: each submission fires the event (no per-form filtering yet)
