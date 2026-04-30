# Project: Centralised Event Builder + URL Trigger

**Status:** Planning — about to enter Active build
**Created:** 2026-04-30
**Owner:** Rohan
**Phases:** 3 (URL trigger primitive → event name dropdowns → centralised builder + grouping)

---

## Why

The plugin currently captures **WooCommerce store owners** very well — WC events are auto-handled, no CSS required. That UX is the proof of concept.

It does **not** capture: B2B lead-gen sites, service businesses, course/community sites, or any non-WC WordPress site that runs forms for conversions. These users:

- Don't have WC events to auto-track
- Need URL- and form-based triggers
- Mostly aren't developers — selector hunting (`form#contact button[type=submit]`) is a paywall
- Don't know which event names to use ("Lead" vs "generate_lead" vs "Contact")
- Have to set up the same conceptual event 5 times across 5 platform pages

This project addresses all four frictions in three layered phases. Together they're a positioning-grade release: *"UniPixel now works for any WordPress site that captures leads — no code, no GTM, no CSS."*

## Out of scope

- **Visual element picker** (popup-navigate-and-point). High build cost, wrong tool for this market — UniPixel's pitch is "simpler than GTM," not "as powerful as GTM."
- **Regex URL patterns.** Wildcard `*` covers 95%+ of cases without bad-input crash surface. Defer to v2 if asked.
- **Value / currency on custom events.** Custom events currently don't carry monetary value. Lead and similar conversion events don't require it. Defer.
- **Generic `form_submit` trigger** without URL pairing. Native browser `submit` event fires on attempt, not success — produces inflated counts. Drop in favour of URL trigger (handles POST-redirect) and plugin-specific integration (handles AJAX).
- **Plugin-specific form integration** (CF7, Gravity, Fluent native PHP hooks). Real next-feature target after this project ships, but its own scope.

---

## Phase 1 — URL trigger + adaptive UI

### What ships

- New `event_trigger` value: `url` ("On Page URL Match")
- Possibly: `form_submit_on_url` ("On Form Submission on URL") — see open questions
- URL pattern matching (wildcard syntax)
- Reusable Page/URL picker UI component
- Adaptive `element_ref` column in custom-events admin tables (label + helper change based on trigger type)

### Decided specs

**Pattern syntax**
- Single special character: `*` (matches zero or more characters)
- Patterns without `*` require exact match
- Match runs against **path + query string** of URL
- Case-insensitive
- Trailing-slash tolerant
- No regex
- Examples surfaced in helper text:
  - `/thank-you/` — exact
  - `/thank-you*` — anywhere starting with `/thank-you` (most common)
  - `*thank-you*` — anywhere containing
  - `*` — any URL on the site
  - `/checkout/?step=2` — exact including query string

**Page/URL picker (reusable component)**
- Dropdown listing all WP pages/posts (via REST API, lazy-loaded if many)
- Picking a page auto-fills the URL pattern with that page's path
- "Any URL" toggle/checkbox writes `*` to the field
- Free-text textbox always editable, always the source of truth — pickers/toggles just write to it
- Used wherever a URL field appears (`url` trigger, `form_submit_on_url` trigger, future)

**Adaptive `element_ref` column** in custom events admin table

| Trigger | element_ref label | Input UI |
|---|---|---|
| On Element Clicked | "CSS selector" | Free text — placeholder `#contact-form` |
| On Element Shown | "CSS selector" | Free text — placeholder `.cta-button` |
| On Page URL | "URL pattern" | Page picker + free text + "Any URL" toggle |
| On Form Submission on URL | "URL pattern" | Same Page/URL picker |

The column polymorphically stores the appropriate value. No schema change.

**Fire timing (URL trigger)**
- Browser-side: fires on `DOMContentLoaded` (early — conversion events are time-sensitive)
- Server-side (CAPI): fires via the existing AJAX path used by every other event in the plugin. Browser-side dispatch detects the URL match and POSTs to the platform's `ajax_data_for_server_event_*` endpoint, which fires the CAPI call.

**NOTE — discovered 2026-04-30 during build:** the original spec said "fires from PHP on matching pageload, similar to existing PageView dispatch." That description was inaccurate — PageView does NOT fire directly from PHP. PageView is injected as a `'shown'` trigger on `body` and dispatched via the same AJAX path. The url-trigger implementation matches this existing pattern exactly. A genuine "direct PHP dispatch" would be a plugin-wide architectural change (lifting PageView + url-trigger together) and is out of scope for Phase 1.

**Fire-once-per-session for URL trigger**
- **Default ON** for `url` and `form_submit_on_url` triggers (these are conversion events; double-firing on reload is undesirable)
- Mechanism: cookie or session storage keyed on `(url_pattern + event_name)`
- Admin toggle to override (off-by-default = once-per-session, on = multi-fire)

**Multiple matches**
- If a URL matches *two* configured URL events, both fire — independent events with independent names
- Same applies to form_submit_on_url

### Schema impact (Phase 1)

None. `event_trigger` is `VARCHAR(255)` already. New values just go in.

---

## Phase 2 — Standard event name dropdowns

### What ships

- `event_name` field in custom events admin tables becomes a combobox
- Per-platform standard event lists (hardcoded JSON mapping)
- "Custom..." option drops back to free text for arbitrary names

### Decided specs

**Per-platform standard event lists** (hardcoded, exposed via PHP and JS):

| Platform | Standard events |
|---|---|
| Meta | AddPaymentInfo, AddToCart, AddToWishlist, CompleteRegistration, Contact, CustomizeProduct, Donate, FindLocation, InitiateCheckout, Lead, Purchase, Schedule, Search, StartTrial, SubmitApplication, Subscribe, ViewContent |
| Google (GA4) | generate_lead, sign_up, login, search, select_content, share, begin_checkout, add_to_cart, view_item, purchase, view_promotion, select_promotion |
| TikTok | AddPaymentInfo, AddToCart, AddToWishlist, ClickButton, CompletePayment, CompleteRegistration, Contact, Download, InitiateCheckout, PlaceAnOrder, Search, SubmitForm, Subscribe, ViewContent |
| Pinterest | AddToCart, Checkout, Custom, Lead, PageVisit, Search, Signup, ViewCategory, WatchVideo |
| Microsoft | (less standardised — recommended set TBD on first build pass) |

**Combobox UX**
- Dropdown shows that platform's standard events
- Bottom option: **"Custom..."** — switches the input to free text
- Tooltip on standard names: "Reported as a Standard Event in [platform]'s Events Manager / Ads Manager"
- Editing an existing event preserves whatever was there (standard or custom — auto-detect on load)

### Schema impact (Phase 2)

None. `event_name` is `VARCHAR(255)` already. UI-only enhancement.

---

## Phase 3 — Centralised conversion builder + grouping

### What ships

- New admin page: **Conversions**
- Builder UI for cross-platform conversion creation
- Conversion grouping via single nullable column on existing events table
- Group view: shared fields propagate, per-platform fields stay local
- Group badge on per-platform rows linking back to the group view
- Detach-from-group affordance

### Decided specs

**Admin layout — Conversions page**

Top-level menu item or sub-page under UniPixel admin (placement: TBD — see open questions).

Lists existing conversion groups. Each row shows:
- Conceptual event name ("Lead Form Submission")
- Trigger summary ("URL: /thank-you/ + form_submit")
- Coverage badge ("4 of 5 platforms")
- Quick-edit / delete

"Create new conversion" button → builder.

**Builder layout**

Top section — shared, applies to all platforms:
- Trigger type (click / shown / url / form_submit_on_url)
- Trigger details (CSS selector OR URL pattern via Page picker)
- Conceptual event picker ("Lead", "Newsletter Signup", "Contact Submitted", "Custom...")
- Description (optional)

Per-platform rows — one per enabled platform:

| Platform | Include? | Event name | Client | Server | Log response |
|---|---|---|---|---|---|
| Meta | ✓ | `Lead` | ✓ | ✓ | ✓ |
| Google | ✓ | `generate_lead` | ○ | ✓ | ○ |
| TikTok | ✓ | `Contact` | ✓ | ✓ | ✓ |
| Pinterest | ✓ | `Lead` | ✓ | ✓ | ✓ |
| Microsoft | ☐ | `lead` | — | — | — |

Each row carries its own toggles. Each row enforces its own platform's rules.

**Conceptual event mapping** (hardcoded JSON, drives the auto-fill of per-platform names):

```
"Lead" → Meta:Lead, Google:generate_lead, TikTok:Contact, Pinterest:Lead, Microsoft:lead
"Newsletter Signup" → Meta:Subscribe, Google:sign_up, TikTok:Subscribe, Pinterest:Signup, Microsoft:signup
"Contact Form Submitted" → Meta:Contact, Google:contact, TikTok:Contact, Pinterest:Lead, Microsoft:contact
"Registration / Sign Up" → Meta:CompleteRegistration, Google:sign_up, TikTok:CompleteRegistration, Pinterest:Signup, Microsoft:signup
"Custom..." → user types one event name; per-platform overrides allowed
```

User can override per-platform name in any row. Mapping is just the smart default.

**Per-platform constraint enforcement (G-001 — Google mutual-exclusion)**

When the conceptual event is *not* Purchase:
- Google row's `Client` and `Server` toggles act as a radio pair — turning one on turns the other off
- Inline note: *"Google allows client OR server tracking for this event type, not both. Both are only allowed for Purchase events."*
- Default state for Google + Lead: Server only (most reliable from MP)

When the conceptual event is Purchase:
- Both toggles independent, no enforcement

Constraint logic lives in shared per-platform rule code used by BOTH the builder AND the per-platform table editor. Single source of truth.

**"Include?" checkbox per platform**

Skipped platforms don't get a row created. User can add them later by editing the conversion and toggling Include on.

**Save behaviour**

- Single save creates one row per included platform in `unipixel_events_settings`
- All rows share the same `conversion_group_id`
- A separate row in a new `unipixel_conversion_groups` table holds the conceptual event name, description, trigger details (denormalised for display, or normalised if we prefer)

**Per-platform tables interaction**

The per-platform tables show the same rows. A row created via the builder appears in the relevant platform's table just like a manually-created one would, with:
- Small group badge: *"Part of: Lead Form Submission"* with a link to the conversion view
- Editing a **shared field** (trigger type, URL pattern, CSS selector) propagates back to the group → all linked rows update
- Editing a **per-platform field** (event_name, send_client, send_server, send_server_log_response) stays local to that row only
- "Detach from group" option for power users — sets `conversion_group_id = NULL` on that row

### Schema impact (Phase 3)

```sql
-- Add nullable group ID to existing custom events table
ALTER TABLE wp_unipixel_events_settings
  ADD COLUMN conversion_group_id INT NULL,
  ADD INDEX idx_conversion_group (conversion_group_id);

-- New table for conversion group metadata
CREATE TABLE wp_unipixel_conversion_groups (
  id INT AUTO_INCREMENT,
  conceptual_event VARCHAR(255) NOT NULL,
  description TEXT,
  event_trigger VARCHAR(255) NOT NULL,
  trigger_target VARCHAR(255) NOT NULL,  -- URL pattern or CSS selector (polymorphic, mirrors element_ref)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
```

`unipixel_conversion_groups.id` ↔ `unipixel_events_settings.conversion_group_id`.

---

## Open questions

1. **Does `form_submit_on_url` ship in Phase 1, or wait?** It depends on whether browser-side native `submit` listening + URL constraint is enough for v1, or whether we'd rather wait and ship plugin-specific integration as a cleaner solution. Default position: ship `url` trigger only in Phase 1, evaluate `form_submit_on_url` after Phase 1 lands.
2. **Builder default state — per-platform rows expanded or collapsed?** Expanded gives full transparency (user sees what's actually being created); collapsed feels cleaner but hides per-platform reality. Lean: expanded by default. Confirm during build.
3. ~~**`send_server_log_response` column on `unipixel_events_settings`**~~ — **Resolved 2026-04-30:** column exists, added via the column-patch path in `config/schema.php` (see `unipixel_setup_separate_transport_settings()` ~line 619). Backed.
4. **Conversions page placement** — top-level UniPixel menu item, or sub-page under existing structure? Top-level signals importance; sub-page keeps menu lean. Defer to UI build.
5. **Microsoft standard events list** — research recommended set during Phase 2 build.
6. **Page picker scope** — pages/posts only, or include custom post types? Probably pages + posts + WC products. Confirm.
7. **Trigger details propagation in groups** — if a user edits the URL pattern on the group view, propagate immediately or prompt for confirmation? Lean: immediate, with undo via revisiting.

## Cross-references

- **Domain rule G-001** (Google client/server mutual exclusion): `.claude/domain-knowledge/platform-discoveries.md`
- **Testing methodology**: `.claude/testing/testing.md`
- **Per-phase test flows**:
  - `.claude/testing/flows/custom-event-url-trigger.md`
  - `.claude/testing/flows/custom-event-form-submit-on-url.md`
  - `.claude/testing/flows/event-name-dropdown.md`
  - `.claude/testing/flows/centralised-conversion-builder.md`
  - `.claude/testing/flows/conversion-group-management.md`

## Definition of done (per phase)

**Phase 1 done when:**
- `url` trigger value works end-to-end (browser fire, CAPI fire, dedup, fire-once-per-session)
- Page/URL picker component renders, lists pages, populates field
- Adaptive `element_ref` column shows correct UI per trigger
- `custom-event-url-trigger` flow runs green with baselines blessed
- (If shipped: `form_submit_on_url` works, `custom-event-form-submit-on-url` flow runs green)

**Phase 2 done when:**
- Each platform's events admin shows event_name as a combobox with that platform's standard events + Custom option
- Free text override works
- Loading existing events correctly re-selects standard names where applicable
- `event-name-dropdown` flow runs green

**Phase 3 done when:**
- Conversions admin page lists existing groups
- Builder creates rows in `unipixel_events_settings` with shared `conversion_group_id`
- G-001 enforced inline for Google rows when conceptual event ≠ Purchase
- Conceptual event mapping auto-fills per-platform event names
- "Include?" per platform works
- Per-platform tables show group badge linking back to group view
- Shared/local field editing propagates correctly
- Detach-from-group works
- Delete group with choice "delete all rows" or "unlink only" works
- `centralised-conversion-builder` and `conversion-group-management` flows run green

## Reflection back to knowledge bases (after ship)

When phases complete, reflect retained patterns and rules into permanent knowledge:
- **Adaptive trigger column pattern + page picker component** → `app-knowledge/app-knowledge.md` (architectural pattern, may be reused for future triggers)
- **Conceptual event mapping** → `domain-knowledge/` (cross-platform event taxonomy — useful reference)
- **Conversion group model** → `app-knowledge/app-knowledge.md` (data architecture)
- This project doc stays in `projects/` with `Status: Complete` once Phase 3 ships.
