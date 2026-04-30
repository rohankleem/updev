# Flow: Centralised conversion builder

**Status:** Draft
**Last run:** —
**Covers:** Phase 3 of [centralised-event-builder](../../projects/centralised-event-builder.md). The new Conversions admin page → builder UI → save creates rows in `unipixel_events_settings` linked by `conversion_group_id`. G-001 enforcement, conceptual event mapping, "Include?" per platform.

## Setup

**Required state delta from baseline:**
- All 5 platforms enabled with test pixel IDs
- WP admin session active
- `unipixel_events_settings.conversion_group_id` column exists (Phase 3 schema)
- `unipixel_conversion_groups` table exists
- No existing conversion groups (clean state)

---

## Scenario 1: Conversions page renders empty state

**Action:** Navigate to UniPixel → Conversions admin page.

**Asserts:**
- Page loads without errors
- Empty-state message: "No conversions configured yet" or similar
- "Create new conversion" button visible

---

## Scenario 2: Create new conversion — builder UI structure

**Action:** Click "Create new conversion."

**Asserts:**
- Builder page loads
- Top section visible: trigger type dropdown, trigger details (adaptive), conceptual event picker, description
- Per-platform rows section visible — one row per enabled platform (5 rows for full setup)
- Each row shows: Include checkbox (✓ by default), Event name input, Client toggle, Server toggle, Log response toggle

---

## Scenario 3: Pick conceptual event "Lead" → per-platform names auto-fill

**Action:** Set trigger = `url`. Set URL pattern = `/thank-you/`. Pick conceptual event "Lead."

**Asserts:**
- Meta row event_name auto-fills with `Lead`
- Google row event_name auto-fills with `generate_lead`
- TikTok row event_name auto-fills with `Contact`
- Pinterest row event_name auto-fills with `Lead`
- Microsoft row event_name auto-fills with `lead` (or platform-recommended)
- Each row's name field is editable — user can override

---

## Scenario 4: Google row enforces G-001 mutual exclusion (non-Purchase)

**Action:** With conceptual event = "Lead" (non-Purchase). On Google's row, both Client and Server toggles are off initially. Toggle Client on.

**Asserts:**
- Google's Server toggle does NOT auto-enable
- Now toggle Server on.
- Google's Client toggle auto-disables (mutual exclusion)
- Inline note visible: "Google allows client OR server tracking for this event type, not both. Both are only allowed for Purchase events."

**Action continued:** Toggle Client back on.

**Asserts:**
- Server toggle auto-disables. Mutual exclusion behaves as a radio pair.

---

## Scenario 5: Purchase conceptual event allows both for Google

**Action:** Change conceptual event to "Purchase" (or whichever conceptual key maps to Purchase). On Google's row, toggle both Client and Server on.

**Asserts:**
- Both toggles can be on simultaneously
- Inline note about mutual exclusion is HIDDEN or replaced with note: "Both client and server allowed for Purchase events (recommended for transaction_id deduplication)"

---

## Scenario 6: Toggle "Include?" off for Microsoft

**Action:** Uncheck the Include checkbox on the Microsoft row.

**Asserts:**
- Microsoft row visually de-emphasises (greyed out, or collapsed)
- Microsoft's other inputs (event_name, toggles) become disabled
- Save proceeds without Microsoft

---

## Scenario 7: Save creates correct DB rows

**Action:** Set up a Lead conversion. Include Meta, Google, TikTok, Pinterest. Exclude Microsoft. Click Save.

**Asserts:**
- New row in `wp_unipixel_conversion_groups` with conceptual_event = "Lead", event_trigger = "url", trigger_target = "/thank-you/"
- 4 rows in `wp_unipixel_events_settings`, all with the same `conversion_group_id` matching the group's id
- Each row has correct platform_id, event_name, send_client, send_server values
- No row created for Microsoft (excluded)

**Captures:**
- Group row → `expected/scenario-7-group-row.json`
- 4 platform rows → `expected/scenario-7-platform-rows.json`

---

## Scenario 8: Per-platform tables show created rows with group badge

**Action:** Navigate to Meta events admin page (existing per-platform UI).

**Asserts:**
- The Lead row created via the builder appears in the table
- Row has a small "Part of: Lead" badge with link to the group view
- Other columns populated correctly (event_name = Lead, client/server toggles match builder values)

**Action continued:** Repeat for Google, TikTok, Pinterest pages — same expectations. For Microsoft: no row.

---

## Scenario 9: Override per-platform event_name in builder

**Action:** Create a new conversion. Pick "Newsletter Signup" conceptual event. On the Meta row, change event_name from auto-filled `Subscribe` to `MyCustomNewsletter`. Save.

**Asserts:**
- Save succeeds
- Meta's row in `wp_unipixel_events_settings` has `event_name = 'MyCustomNewsletter'`
- Other platforms' rows have their respective auto-filled names (Subscribe / sign_up / Subscribe / Signup)

---

## Scenario 10: Custom conceptual event

**Action:** Create new conversion. Pick conceptual event = "Custom..." Type `MyCustomConversion`. Save.

**Asserts:**
- All included platform rows have event_name = `MyCustomConversion` (or each platform overrides individually if entered before save)
- Group row's conceptual_event = "Custom: MyCustomConversion" (or similar)

---

## Known gaps

- Doesn't test the runtime firing of these events — that's covered by `custom-event-url-trigger.md` (URL trigger runtime) and `custom-event-form-submit-on-url.md`
- Group editing/deletion/detach behaviour covered by `conversion-group-management.md`
- Doesn't test importing pre-existing rows into a group (no UI for that yet)
