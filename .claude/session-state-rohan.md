# Session State — Rohan

> **⚠ Bypass permission mode is ON for this project.** `permissions.defaultMode: bypassPermissions` is set in `.claude/settings.local.json`. Every Claude Code session in this project starts with no permission prompts. Set during the v2.6.6 testing-pass setup (2026-05-03). **Remove the line when testing is done** to restore the safety net for routine work. CLAUDE.md autonomy rules (commit / push / deploy / release / version-bump = explicit instruction only) still apply — the model still follows them; it's only the *harness-level prompt layer* that's off.

---

## Where We Came From

- Token-Acquisition UX initiative (Phases 1–8) shipped 2026-05-12 to 2026-05-13. Project doc marked Complete. All Test Connection / status strip / wizard / 14-day grace / plain-English label / help-icon work done across Meta, Google, TikTok, Pinterest, Microsoft. Committed as `739c49d`.
- Verified end-to-end in browser on updev.local.site this session: all 7 surfaces (Home dashboard, Meta/Google/TikTok/Pinterest/Microsoft setup pages, Stored Event Logs) render correctly, Meta Test Connection live-passed against real token.
- Release for v2.7.0 still staged but not cut.

---

## What We Worked On

### Microsoft event-name mismatch — diagnostic + parked

The Token-Acquisition browser verification turned into a real-world investigation of Rohan's own live site (steelchief.com.au), where Microsoft Ads is recording two custom Site Event goals (`ClickOpenConfigurator`, `ConfiguratorShownPrice`) but failing to record three others (`Purchase 2`, `Lead`, `ConfiguratorSubmittedYes`) despite the underlying events firing in UniPixel's Stored Event Logs.

**Diagnosis (high confidence):** Microsoft Ads conversion goal matching is case-sensitive on the rule's `EventAction` string. The `Purchase 2` goal's rule is literally `EventAction = "Purchase" (EqualsTo)` — confirmed via Microsoft's own Copilot. UniPixel sends `purchase` lowercase. Case mismatch = zero matches. Same pattern applies to the Lead goal. The two Configurator goals work because their values are user-typed Bespoke fields matching the goal name's capitalisation.

**Underlying root cause:** Microsoft has no canonical UET event-name standard. Their docs are inconsistent — `purchase` lowercase in some examples, `PRODUCT_PURCHASE` uppercase in others, `AutoEvent_purchase` mixed, plus a broken 404 on their own "How to track custom events" help link. Microsoft's own support specialist refused to publicly answer the "what name should I send?" question on their Q&A site. The "convention" depends on which Microsoft tool created the goal — most users go through the Goal Category dropdown which auto-fills PascalCase rules.

**Tried and reverted:** Option A — hardcode PascalCase Microsoft event names in UniPixel (10 files edited, DB migration written, PHP lint clean). Reverted because Option C (make `event_platform_ref` user-editable per row in the WooCommerce events table, matching how Site Events already works) is a much better fit. Microsoft having no standard means no hardcoded value can be universally correct.

Full writeup with all evidence, three solution options, sibling issues, and Option A implementation notes if we change our minds: `.claude/projects/microsoft-event-name-mismatch.md`.

### Two sibling issues parked alongside

Both surfaced during the same investigation, both unresolved, both documented in the project doc above:

1. **Server-side Microsoft rows missing from Stored Event Logs** — likely cause is `serverside_global_enabled = 0` at the platform level on live (separate from per-event Send Server-side toggles). Code gates server firing on the platform flag.
2. **Three misconfigured Site Event triggers** — `ConfiguratorSubmittedYes` URL pattern probably doesn't match the real post-submit URL; `lead/#thankyousuccess` element may not exist in DOM on lead success; `lead/#bookCallFormSubmitted` has a CSS-selector value in a URL-Match trigger.

---

## Where We Need To Go

### Pick the solution for the Microsoft event-name issue (next session)

Read `.claude/projects/microsoft-event-name-mismatch.md`. Decision needed:

- **Option C** (preferred direction): make `event_platform_ref` editable per row in the WooCommerce events admin table. Mirrors Site Events. Requires adding a stable internal-key column to `wp_unipixel_woocomm_event_settings` (so DB lookups stay anchored while the wire-format value becomes user-controlled), updating hook handlers, updating `admin/page-microsoft-events.php`, plus migration to set sensible PascalCase defaults.
- **Option B** (translation layer): keep DB lowercase, translate at send-time via a map. Simpler but still wrong for non-default Microsoft setups.
- **Option A** (hardcode PascalCase): fastest, gets Rohan recording revenue on live faster but is one-size-fits-all in a domain without a fits-all answer. Implementation notes preserved in the project doc.

### Followups regardless of which option

- Fix the live site's two Microsoft Site Events rows: `lead` → `Lead` (manually, in UniPixel admin)
- Verify and toggle `serverside_global_enabled = 1` for Microsoft on the live site to start getting server-side rows in logs
- Fix the three broken Site Event triggers — needs live-site browser inspection of the configurator submission flow

### Release backlog (unchanged)

The Token-Acquisition UX initiative (Phases 1–8) is still staged for v2.7.0 release. The Microsoft event-name fix should ride along once a solution is picked. Standard release-gate process per CLAUDE.md § Release Gate + `app-knowledge/deploy-and-release.md`.

### Marketing follow-ups (unchanged)

- v2.7.0 announcement blog post when release ships (Token-Acquisition UX as headline, possibly plus a "we now record Microsoft Ads conversions correctly" note depending on what we ship for Microsoft).
- Refresh Meta and Pinterest docs at unipixelhq.com/unipixel-docs/ — stale.
- "How UniPixel tells you what's wrong" competitive piece on diagnostic feedback.

### Memories saved this session

None new. Existing memories continued to apply (no em dashes, no time estimates, ground strategy in user moments, drafts-are-done, don't project in design conversations).
