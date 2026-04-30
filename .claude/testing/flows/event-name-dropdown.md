# Flow: Event name dropdown — admin UI

**Status:** Draft
**Last run:** —
**Covers:** Phase 2 of [centralised-event-builder](../../projects/centralised-event-builder.md). The `event_name` field in custom-events admin tables becomes a combobox with platform-specific standard events + "Custom..." escape hatch.

## Setup

**Required state delta from baseline:**
- WP admin session active
- At least one platform enabled (start with Meta)
- Empty custom events table for that platform (or known starting state)

---

## Scenario 1: Add new event row — dropdown shows platform standard events

**Action:** Navigate to Meta events admin page. Click "Add new event row." Click the `event_name` dropdown.

**Asserts:**
- Dropdown lists Meta's standard events: AddPaymentInfo, AddToCart, AddToWishlist, CompleteRegistration, Contact, CustomizeProduct, Donate, FindLocation, InitiateCheckout, Lead, Purchase, Schedule, Search, StartTrial, SubmitApplication, Subscribe, ViewContent
- Bottom option: "Custom..."
- No standard events from other platforms appear in the list

---

## Scenario 2: Pick a standard name → field stores correctly

**Action:** Pick "Lead" from dropdown. Fill remaining fields. Save.

**Asserts:**
- Form submission succeeds
- DB row in `wp_unipixel_events_settings` has `event_name = 'Lead'`
- Reload page — dropdown re-selects "Lead"

---

## Scenario 3: Custom name escape hatch

**Action:** Add new row. Pick "Custom..." in dropdown. Field switches to free text. Type `myFormSubmission`. Save.

**Asserts:**
- Form submission succeeds
- DB row has `event_name = 'myFormSubmission'`
- Reload page — field shows free text mode with `myFormSubmission` populated (not in dropdown options)

---

## Scenario 4: Editing existing event with non-standard name

**Setup additions:**
- Pre-existing row in `wp_unipixel_events_settings` with `event_name = 'oddCustomName'` (didn't come from dropdown, manually inserted)

**Action:** Open Meta events admin. Look at the existing row.

**Asserts:**
- Field detected this isn't a standard name → shows in free-text "Custom..." mode automatically
- Value `oddCustomName` displayed correctly
- User can switch back to dropdown via a "Use standard name" affordance (or by clearing)

---

## Scenario 5: Per-platform list correctness

**Action:** Repeat Scenario 1 for each platform admin page (Google, TikTok, Pinterest, Microsoft). Click the event_name dropdown.

**Asserts (per platform):**
- Google shows: generate_lead, sign_up, login, search, select_content, share, begin_checkout, add_to_cart, view_item, purchase, view_promotion, select_promotion
- TikTok shows: AddPaymentInfo, AddToCart, AddToWishlist, ClickButton, CompletePayment, CompleteRegistration, Contact, Download, InitiateCheckout, PlaceAnOrder, Search, SubmitForm, Subscribe, ViewContent
- Pinterest shows: AddToCart, Checkout, Custom, Lead, PageVisit, Search, Signup, ViewCategory, WatchVideo
- Microsoft shows: (recommended set TBD during build)
- Each list ends with "Custom..."

---

## Scenario 6: Tooltip surfaces

**Action:** Hover over a standard event name in the dropdown.

**Asserts:**
- Tooltip text: "Reported as a Standard Event in [platform]'s Events Manager / Ads Manager" (or similar wording)

---

## Known gaps

- Doesn't test the centralised builder's event name behaviour — that's covered by `centralised-conversion-builder.md`
- Per-platform standard event lists are hardcoded in JS/PHP — list correctness verified once per build, then captured as baseline
