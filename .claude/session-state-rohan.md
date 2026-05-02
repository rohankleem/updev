# Session State — Rohan

---

## Where We Came From

- `updev` dev base set up (cloned from uphq, points at local `updev` MySQL DB, lives at `https://updev.local.site` + remote `https://dev.unipixelhq.com`).
- Major docs refactor moved plugin docs to repo-root `.claude/` with operating manual, app-knowledge / domain-knowledge / marketing-knowledge / projects / testing folders.
- Testing scaffold built: `.claude/testing/` with methodology, 14 flow files, captured baselines for url-trigger and event-name-dropdown flows.
- v2.6.5 shipped to wp.org: 5 popup layouts, optional Reject all button, CookieAdmin support, mobile-responsive popup, animation centering fix, popup asset cache-bust, consent multi-language work (i18n).

---

## What We Worked On

### v2.6.6 shipped — Centralised Event Manager

Designed, built, tested, released the **Centralised Event Manager** as v2.6.6. Three phases shipped together:

- **Phase 1 — URL trigger.** New `url` event_trigger value. Wildcard pattern matching (PHP + JS, identical semantics, 26-case parity verified). Adaptive `element_ref` UI (CSS selector vs URL pattern). Browser-side dispatch wired into all 5 platform watchers. Fire-once-per-session guard via sessionStorage (consent-aware after a bug fix during testing).
- **Phase 2 — Standard event name dropdowns.** Per-platform standard event lists (Meta / Google / TikTok / Pinterest / Microsoft). Combobox with auto-detect on load + "Custom..." escape hatch. Frontend-only — no schema change.
- **Phase 3 — Cross-platform conversion builder.** New top-level admin page "Event Manager". Builder UI with trigger-config + per-platform rows + conceptual event auto-fill (Lead → Meta:Lead, Google:generate_lead, TikTok:Contact, etc.). Reusable Page/URL picker (REST page list + 3-mode radio: pick page / any URL / custom). G-001 Google client/server mutex enforced inline. "Include this platform" checkbox per platform. No-platforms-enabled warning with quick links. Schema: new `unipixel_conversion_groups` table + `conversion_group_id` column on `unipixel_events_settings` (in `$table_schemas` for fresh install + patch function for upgrade safety).

Smoke-tested end-to-end via Claude in Chrome MCP: created Lead conversion across Meta + Google, verified DB rows, verified G-001 enforcement, verified URL match correctness on `/contact*`, verified fire-once on reload, verified empty-platform warning state.

### Release flow

Bumped 2.6.5 → 2.6.6 across all 4 release-gate files. Pre-export checks clean (smart quotes scan, `php -l` on all source). Obfuscation export to scratch folder. Post-export checks clean (`php -l` filename + stdin, `.claude/` and `CLAUDE.md` correctly excluded). Rohan uploaded to wp.org SVN.

### Marketing docs refresh

All 5 marketing docs updated to reflect v2.6.6:
- **positioning.md** — Pillar 3 rewritten around lead-gen + cross-platform one-shot setup. Two new genuine differentiators (Centralised cross-platform event setup, Lead-gen / non-WC first-class). New audience C5 (lead-gen / B2B / service / course / membership WP sites). 3 new rows in competitor matrix where UniPixel is YES and every competitor is NO. Per-competitor "why we win" pitches updated.
- **priorities.md** — v2.6.6 added to "What's done". CSS-selector blocker reframed (substantially reduced — only click/shown still need selectors; URL-based covers most lead-gen). Tier 2 restructured.
- **unipixelhq-content.md** — Custom Event Tracking doc flagged as now-outdated. Three new blog candidates queued (v2.6.6 announcement, lead-gen tracking on WP without code, lead-gen alternative to PYS).
- **campaigns.md** — 7 new Pinpoint hooks for Campaign 2 (Meta/Insta graphics). New Lead-Gen / Non-WooCommerce ad group for Campaign 4 (Google Ads Universal) with phrase-match keywords + headlines.
- **writing-style.md** — Em-dash rule extended: avoided by default in plugin admin copy (matches the saved memory).

### Code commit + push

One commit `fca0421` on `main`, pushed to GitHub. 93 files changed including all v2.6.6 work + previously-uncommitted v2.6.5 popup work + testing scaffold + project docs + marketing refresh.

### Memories saved

- Don't project next steps in design conversations — answer the question, then wait for direction.
- No em dashes in user-facing plugin copy — periods/commas/colons instead. Internal docs and chat are fine.

---

## Where We Need To Go

### Phase 3 follow-ups (small, deferred)

- **Per-platform table: group badge + link back to Event Manager.** When a row in a platform's events admin is part of a conversion group, show a small "Part of: Lead" badge. Not blocking; nice-to-have for discoverability.
- **Reflect Phase 3 retained knowledge into permanent docs.** Per the project doc: Page picker component pattern + adaptive trigger column → `app-knowledge.md`; conceptual event mapping → `domain-knowledge/`; conversion group data model → `app-knowledge.md`. Quick docs pass.

### Content follow-ups from v2.6.6

- **Custom Event Tracking doc rewrite** at `unipixelhq.com/unipixel-docs/custom-event-tracking/` — now significantly outdated (predates URL trigger + Event Manager).
- **Cookie Consent doc refresh** — also outdated (predates v2.6.4 + v2.6.5 popup work).
- **v2.6.6 announcement blog post** — captures the moment.
- **"Track lead-gen on WordPress without code"** — opens the new C5 audience.
- **Lead-gen alternative to PYS** — competitive piece for the new audience.

### Backlog still on the radar

- **Visual element picker** for click/shown triggers (residual after v2.6.6 — lower priority now).
- **Setup wizard / onboarding flow** (tier 2).
- **Event diagnostics dashboard** (tier 2 — "is it working?" health screen).
- **PHP validation gap** — empty access token + serverside_global_enabled ON = silent API failures.
- **Multi-tier click ID persistence** (spec at `.claude/projects/multi-tier-clickid-persistence.md`).
- **TikTok expanded event coverage** — AddPaymentInfo, CompleteRegistration, SubmitForm.
- **Stored Event Logs UX** — intro text, filtering, surface the log-response toggle requirement.
- **Microsoft CAPI live-endpoint test** — still token-gated.

### Growth execution

| Priority | Channel | Status |
|---|---|---|
| 1 | Reddit + WP forums | Active — 3-5 helpful replies/week |
| 1b | Google Ads | Live — Campaign 3 (competitor) + Campaign 4 (universal). Lead-gen ad group ready to add post-v2.6.6. |
| 1c | Meta/Insta Ads | Running — new v2.6.6 Pinpoint hooks ready for Graphic 4 |
| 2 | YouTube tutorials | Not started |
| 3 | Blog content | 5 articles live; 3 new candidates queued post-v2.6.6 |
