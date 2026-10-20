# Session State — Rohan

> **⚠ Bypass permission mode is ON for this project.** `permissions.defaultMode: bypassPermissions` is set in `.claude/settings.local.json`. Every Claude Code session in this project starts with no permission prompts. Set during the v2.6.6 testing-pass setup (2026-05-03). **Remove the line when testing is done** to restore the safety net for routine work. CLAUDE.md autonomy rules (commit / push / deploy / release / version-bump = explicit instruction only) still apply — the model still follows them; it's only the *harness-level prompt layer* that's off.

---

## Where We Came From

- 2.6.9 through 2.6.13 shipped (details in `projects/release-log.md`).
- 2026-10-15: adoption drop-off investigation (`projects/adoption-dropoff-investigation.md`) and the ranked fix plan (`projects/fix-plan-2.6.13.md`). Its Tier 1 batch shipped in 2.6.13.

## What We Worked On

### Google client/server rule re-check, fix-plan #21 (2026-10-20)

- **G-001 re-checked against Google's docs** (`domain-knowledge/platform-discoveries.md` § G-001):
  - Through the Measurement Protocol, GA4 still dedupes only `purchase`, on `transaction_id`.
  - The Data Manager API dedupes any event by `transactionId`. For GA4 that's allowlisted properties only; for Google Ads it's open to all accounts since 2026-02-10.
  - Google tag gateway keeps a single browser stream. Site Kit runs it as a PHP relay.
- **#21 fixed and committed:** Google page view now defaults to browser only, on clean installs and in the Google setup wizard (`config/schema.php`, `admin/js/google-setup-wizard.js`).
  - Before, both browser and server were on, which double counts in GA4 once Google server-side is switched on.
  - Existing installs are unchanged.
- **Fix plan additions:**
  - #34: GA4 server events drop the visitor's IP and user agent (fix: send `ip_override` and `user_agent`).
  - #36: a setup-page save turns browser page view back on.
  - A policy item for existing installs still on the old page view default.
- **The session went badly:** a two-line fix took most of an hour because of unapproved code edits and shifting descriptions of the issue. Lessons are in Claude's memory (`answer-the-question-asked`).

## Where We Need To Go

- Fix-plan #34 and #36, when Rohan says go.
- Decide whether to migrate existing installs off the old Google page view default.
- Google strategy needs a plan: a tag gateway PHP relay, and the Data Manager API for Google Ads conversions. A planning session was proposed on 2026-10-20.
- Still uncommitted from 2026-10-15:
  - `projects/fix-plan-2.6.13.md` and `projects/adoption-dropoff-investigation.md` (both untracked)
  - that session's notes in `platform-discoveries.md` (RQ-003/004, MS-001, PIN-002, G-003, WP-002 to WP-004, CONSENT-001)
- Carried over from June, status not re-checked: Microsoft event-name mismatch, Option C (`projects/microsoft-event-name-mismatch.md`).

### Memories
New: `answer-the-question-asked` (feedback, 2026-10-20).
