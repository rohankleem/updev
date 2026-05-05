# Event Terminology Framework

The single source of truth for how UniPixel names, orders, and explains event-tracking concepts across admin UI, docs, marketing, and code comments.

When this doc and any other surface disagree, this doc wins. Other surfaces should be brought into line.

`vocabulary.md` carries the short pinned definitions; this file carries the structural framework, ordering rules, copy patterns, and per-platform mapping.

---

## Why this exists

Multiple admin surfaces let users configure events: per-platform Events Setup pages, the centralised Event Manager, the per-platform Custom Events table, and (eventually) docs and marketing copy. Without one shared vocabulary and copy framework, the user hits a different label, popover, and column order on every screen and has to re-learn the same concept several times.

The goal is to disambiguate at the moment of entry: every label is paired with enough inline support that the user doesn't need to ask "what does this mean?" or "is this the same as the other one?"

---

## The terminology hierarchy

```
Event
├─ WooCommerce events     (auto, hook-based, per-event toggles + Apply Recommended Settings)
└─ Site events            (user-configured: clicks, visible elements, page URLs, form submissions on URL)
   ├─ Standard            (Platform Event Reference picked from the platform's recognised list)
   └─ Bespoke             (Platform Event Reference is a free-form name of the user's choice)
```

**Single source of truth term list:**

| Term | Definition | When to use |
|---|---|---|
| **Event** | The umbrella concept. Anything reported to a platform. | Always preferred over "conversion" in structural naming. |
| **WooCommerce events** | Hook-based events fired automatically from WC actions (AddToCart, InitiateCheckout, Purchase, etc.). Cross-platform by nature. | Section heading on per-platform Events pages. |
| **Site events** | Everything else: events the user configures themselves on non-WC interactions. | Replaces "Custom Events" as the section concept (see migration note below). |
| **Standard event** | A site event whose Platform Event Reference is from the platform's recognised list (Meta `Lead`, Google `generate_lead`, etc.). | Inside Site events context. |
| **Bespoke event** | A site event with a free-form Platform Event Reference. | Inside Site events context. Replaces "Custom event" in the *free-form* tier specifically. |
| **Trigger** | When the event fires: click, shown, url, form_submit_on_url. | Field name in builder + tables. |
| **Trigger Target** (structural / docs term) / **Acts On** (user-facing label) | The thing the trigger acts upon: a CSS selector for click/shown, a URL pattern for url/form_submit_on_url. Polymorphic — same column, different shape per trigger. | Structural docs / framework prose: "Trigger Target" — pairs cleanly with "Trigger" when explaining the data model. User-facing column header / form label: **"Acts On"** — plain English derived from "the thing the trigger acts upon", more accessible to non-technical users. The user-facing label stays constant; the help sub-line under it adapts ("CSS selector for the element to track" / "URL pattern that the page must match. Use * as wildcard."). |
| **Platform Event Reference** | The string sent to the platform's pixel/CAPI as the event identifier. | Full prose: "Platform Event Reference". Column headers: "**Platform Event Ref**" (the "Platform" prefix is load-bearing — never drop it; abbreviating "Reference" to "Ref" is fine when space is tight). |
| **Description** | The user's internal note. Not transmitted. | Same in every surface. |
| **Cross-platform event** | An event configured once that creates linked rows across multiple platforms. | Replaces "conversion" in the centralised builder context. |
| **Conversion** | Reserved for marketing copy and SEO. Not used in structural naming. | unipixelhq.com, blog posts, ad copy. Never in admin labels or doc field names. |

### Migration: "Custom Events" → "Site events" (admin completed 2026-05-03)

The admin UI is an **internal surface**, so it adopts the framework vocabulary in full. As of 2026-05-03, all per-platform Events Setup pages use the section heading "Site Events for [Platform]". Inside the section, events have a Standard or Bespoke Platform Event Reference. The home dashboard tile blurbs were also updated.

### "Custom" is retained externally for customer recognition

In **customer-external surfaces** (unipixelhq.com pages, blog posts, ad copy, support search results, marketplace listings, the wordpress.org plugin page), keep "Custom Events" as the discoverable term. The industry uses it; users searching "custom events" or comparing UniPixel to GTM expect to find it. **Door retained, room refurbished:** customers find us through the familiar word, then we educate them on the refined Standard/Bespoke model once they're inside.

What counts as **external** for this rule: anything indexed by search engines, anything that appears in marketplace/listing copy, anything in our ad creative. What counts as **internal**: the admin UI (per-platform pages, builder, dashboard, settings), onboarding wizards, in-product help text, internal docs. The admin is internal even though end-users see it — the gating factor is "is this surface SEO-relevant", not "does a user read it".

### Bridge pattern: internal labels point to external docs

When an internal surface links to documentation that hasn't yet been rewritten to the new vocabulary (e.g. "Learn how to set up site events" → unipixelhq.com/unipixel-docs/custom-event-tracking/), the **link label** uses the new internal vocabulary; the **URL** stays aligned with the external doc's slug for SEO continuity. The doc gets rewritten on its own schedule, then the URL slug catches up via redirect. This keeps internal consistency without breaking SEO or doing big-bang migrations.

Bridges in use today:
- "Learn how to set up site events" → `unipixelhq.com/unipixel-docs/custom-event-tracking/` (label updated 2026-05-03; doc + slug to be rewritten)

---

## Field set + ordering

Every surface that lists or builds a site event uses the same five-field shape in the same order:

```
1. Trigger
2. Trigger Target
3. Platform Event Reference
4. Description
5. Send / Log toggles  (Send Client-side, Send Server-side, Log Server-side Response)
```

WooCommerce event tables follow the same shape with `Trigger = "Hook-based (automatic)"` and `Trigger Target = "—"` (or omitted in narrow tables).

The centralised builder reads top-to-bottom in this order: Trigger card → Trigger Target → Platform Event Reference (per platform row) → Description.

Where the per-platform Custom Events tables currently have a different column order (Element Reference first, Trigger second), that's a known divergence — the JS row template needs co-ordinating before the columns can swap. Reordering is a separate sweep.

---

## Copy rules

### Inline support text vs popovers

| Where | Use | Why |
|---|---|---|
| Builder cards (centralised Event Manager) | **Inline support sentence** in muted/small text under the card title or label. | Lots of vertical room. Users read top-to-bottom. |
| Per-platform Custom Events table (column headers) | **Short header + tiny sub-line** + popover icon for the longer explanation. | Columns are space-constrained. |
| Inline next to a single input | **Muted small support sentence** if there's room; popover only if the layout is tight. | Default: prefer inline. Popovers cost a click. |

Tasteful, not exhaustive: one short sentence. Skip altogether if the label is self-explanatory.

### Single canonical form for bespoke examples

When showing examples of user-defined identifiers across multiple platforms (bespoke event names, group names, descriptions), use **one canonical form everywhere**. The current canonical form is **`MyBespokeEvent`** (PascalCase, no separators).

Why: the plugin doesn't transform names. Showing `my_bespoke_event` on a Google row and `MyBespokeEvent` on a Meta row implies hidden case conversion. It doesn't happen.

Standard names are different — they *are* platform-native by design and must be shown in their native form (`Lead` for Meta, `generate_lead` for Google, `lead` for Pinterest).

### Per-platform inline hints (Platform Event Reference)

These are the canonical sentences for the Platform Event Reference field, per platform. Use verbatim where space allows; truncate to the second clause for tight headers.

| Platform | Inline hint |
|---|---|
| Meta | "Sent to Meta. Pick a Standard name (e.g. `Lead`) or use a Bespoke name (e.g. `MyBespokeEvent`)." |
| Google | "Sent to Google Analytics. Pick a Standard name (e.g. `generate_lead`) or use a Bespoke name (e.g. `MyBespokeEvent`)." |
| TikTok | "Sent to TikTok. Pick a Standard name (e.g. `Contact`) or use a Bespoke name (e.g. `MyBespokeEvent`). Avoid TikTok Reserved names for Bespoke values." |
| Pinterest | "Sent to Pinterest. Pick a Standard name (e.g. `lead`) or use a Bespoke name (e.g. `MyBespokeEvent`)." |
| Microsoft | "Sent to Microsoft Ads (as the UET Event Action). Pick a Standard name (e.g. `purchase`) or use a Bespoke name (e.g. `MyBespokeEvent`)." |

These use the **internal vocabulary** (Standard / Bespoke) consistently across platforms. Each platform's own term for the recognised tier (Meta says "Standard event", Google says "Recommended event", Microsoft has none) is intentionally not surfaced here — admin is internal, so we keep one vocabulary. The platform-native casing of the example (`Lead` vs `generate_lead` vs `lead`) does the platform-specific teaching without needing to name their tier.

---

## Platform-native terminology mapping

Industry research summary. Captured here so future copy and validation logic can reference one place.

| Platform | Their term for the field | Their term for the recognised tier | Their term for free-form tier | Field/key in payload | Native casing |
|---|---|---|---|---|---|
| **Meta** | Event name | Standard event | Custom event | `event_name` | PascalCase |
| **Google (GA4)** | Event name | Recommended event | Custom event | `event_name` | snake_case |
| **TikTok** | Event name | Standard event (plus Reserved events as a gotcha) | Custom event | `event` | PascalCase |
| **Pinterest** | Event name | Standard event | Custom event (with 6 sub-types: custom, lead, search, signup, view_category, watch_video) | `event_name` | snake_case (newer) / PascalCase (older) |
| **Microsoft (UET)** | Event Action | (no formal "standard" list) | Custom event | `Action` | user-defined |

Cross-platform consensus: 4 of 5 call the field "event name". Microsoft's "Action" is the outlier. "Standard" wins over "Recommended" on adoption (4 of 5).

UniPixel's umbrella term **Platform Event Reference** intentionally diverges from "event name" because in our cross-platform context the user has multiple references to track (trigger target, local ref, group id) and "event name" alone is too generic.

---

## Platform-specific quirks worth surfacing

These belong inline in the bespoke flow as warnings / validations, not just buried in docs. Cross-referenced from `platform-discoveries.md`.

- **TikTok Reserved events** — names that auto-map to standard events. If a user types one as a Bespoke name, TikTok silently maps it to the standard event. Surface inline when the bespoke value matches a known reserved name.
- **Pinterest custom-event sub-types** — Pinterest's "custom" tier accepts only 6 values (`custom`, `lead`, `search`, `signup`, `view_category`, `watch_video`). Anything else gets dropped. Validate on input.
- **Google snake_case rule** — GA4 names must start with a letter and contain only letters/numbers/underscores. PascalCase like `MyBespokeEvent` is technically valid (no special chars). Off-style but accepted. No validation needed.
- **Meta standard-name pattern flagging** — Meta will flag any custom name that contains a substring like "Price", "Lead", "Purchase" by pattern, even when no monetary data is sent. Documented in META-001. Don't try to suppress; it's platform overreach.

---

## Cross-references

- **Pinned definitions:** `vocabulary.md`
- **Platform-specific findings:** `platform-discoveries.md`
- **Centralised Event Manager spec:** `../projects/centralised-event-builder.md`
- **Operating-manual glossary:** `/CLAUDE.md` § Glossary

## What this framework should drive

When new admin UI is built, when copy is updated, when docs are written, when marketing pages are revised — read this file first. If a term is missing, propose an addition rather than coining a synonym in place.
