# Flow: Conversion group management

**Status:** Draft
**Last run:** —
**Covers:** Phase 3 of [centralised-event-builder](../../projects/centralised-event-builder.md). Existing group lifecycle: list view, edit shared fields (propagation), edit per-platform fields (local), detach from group, delete group, add missing platform.

## Setup

**Required state delta from baseline:**
- One existing conversion group, created via the builder, covering Meta + Google + TikTok + Pinterest (Microsoft excluded), Lead conceptual event, URL trigger `/thank-you/`
- (Use the captures from `centralised-conversion-builder` Scenario 7 as the starting state)
- WP admin session active

---

## Scenario 1: Conversions list shows group with summary

**Action:** Navigate to UniPixel → Conversions.

**Asserts:**
- One row visible: "Lead" conversion
- Summary shows: trigger type ("URL"), trigger target ("/thank-you/"), coverage badge ("4 of 5 platforms")
- Action affordances: Edit, Delete

---

## Scenario 2: Edit shared field — URL pattern propagates

**Action:** Click Edit. Change URL pattern from `/thank-you/` to `/thank-you-page/`. Save.

**Asserts:**
- Group row in `wp_unipixel_conversion_groups`: `trigger_target = '/thank-you-page/'`
- ALL 4 linked rows in `wp_unipixel_events_settings`: `element_ref = '/thank-you-page/'`
- Per-platform tables reflect the new value

---

## Scenario 3: Edit per-platform field stays local

**Action:** Open the group's edit view. On the Meta row, change `send_server_log_response` from on to off. Save.

**Asserts:**
- Meta's row in `wp_unipixel_events_settings`: `send_server_log_response = 0`
- Google, TikTok, Pinterest rows: unchanged
- Group row: unchanged (this is a per-row field, not a shared one)

---

## Scenario 4: Edit per-platform event_name override

**Action:** On the group's edit view, change Google's event_name from `generate_lead` to `lead_signup`. Save.

**Asserts:**
- Google's row: `event_name = 'lead_signup'`
- Other platforms: unchanged
- Per-platform table for Google: shows the override

---

## Scenario 5: Detach single platform from group

**Action:** On the per-platform table for TikTok, find the linked Lead row. Click "Detach from group." Confirm.

**Asserts:**
- TikTok's row: `conversion_group_id = NULL`
- Group's coverage badge updates: "3 of 5 platforms"
- Group view no longer shows TikTok in the per-platform rows
- TikTok's event still works at runtime — detached doesn't mean disabled

---

## Scenario 6: Add missing platform to existing group

**Action:** Open group edit view. Microsoft is shown with Include unchecked. Toggle Include on. Set event_name = `lead`. Save.

**Asserts:**
- New row created in `wp_unipixel_events_settings` with Microsoft's platform_id and same `conversion_group_id`
- Group's coverage updates: "4 of 5 platforms" (or "5 of 5" if TikTok is still linked from earlier scenarios)

---

## Scenario 7: Delete group — choose unlink only

**Action:** From Conversions list, click Delete on the Lead group. Dialog asks: "Delete linked rows OR unlink and keep?" Pick "Unlink only."

**Asserts:**
- Group row in `wp_unipixel_conversion_groups`: deleted
- All linked event rows: still exist, but `conversion_group_id = NULL`
- Per-platform tables still show the events; group badge gone
- Events still fire at runtime

---

## Scenario 8: Delete group — choose delete all

**Action:** Re-create the Lead group via the builder. Then from Conversions list, click Delete. Pick "Delete all linked rows."

**Asserts:**
- Group row: deleted
- All linked event rows: deleted from `wp_unipixel_events_settings`
- Per-platform tables: no longer show the events
- Events no longer fire at runtime

---

## Scenario 9: G-001 enforcement preserved on group edit

**Action:** Open existing Lead group edit view. On Google's row, current state is Server only. Toggle Client on.

**Asserts:**
- Server toggle auto-disables (mutual exclusion still enforced when editing existing groups, not just creating new)
- Inline note still appears

---

## Scenario 10: Changing conceptual event on existing group

**Action:** Open Lead group edit view. Change conceptual event from "Lead" to "Newsletter Signup." Save.

**Asserts:**
- User prompted: "This will update event names across all linked platforms. Confirm?"
- On confirm: each row's event_name auto-updates to the Newsletter Signup mapping (Meta:Subscribe, Google:sign_up, etc.)
- Per-platform overrides previously set (e.g. from Scenario 4) are preserved if the user selected "Keep my overrides" — or replaced if they didn't (TBD UX detail)

---

## Known gaps

- Importing existing pre-grouping rows into a new group: no UI yet, may be a v2 feature
- Bulk operations across multiple groups (e.g. disable all groups) not covered
- Concurrent editing (two admins editing the same group simultaneously): out of scope, last-write-wins
