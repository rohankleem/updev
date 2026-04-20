# Session State — Rohan

---

## Where We Came From

- Microsoft Conversions API (CAPI) — full implementation shipped as v2.6.0
- Google Ads campaigns went live; advertiser verification finally unblocked serving (24 March)
- v2.6.0 ready for obfuscation export — not yet deployed to wordpress.org
- AddToCart quality improvement shipped — fragment-based client pixel for AJAX add-to-cart
- Multi-tier click ID persistence spec written; feature not started

---

## What We Worked On

### Set up `updev` as the dev base

- Cloned uphq → `C:\xampp\htdocs\updev\`, pointed at existing `updev` MySQL DB, sanitised `.env` (blanked SMTP + Monday API token).
- Pulled unipixel plugin source + `_obf/` from `website-sheds` (which is now retired).
- composer install + npm install done locally.
- Git repo: `github.com/rohankleem/updev` on `main` (initial push unblocked false-positive Mapbox-demo-token scanner).
- Remote subdomain `dev.unipixelhq.com` created on Interserver (DirectAdmin). Docroot `/domains/dev.unipixelhq.com/public_html`, PHP 8.3.
- Deploy scripts in `_deploy/` point at `domains/dev.unipixelhq.com`; `.env` was created server-side.
- Site is live locally at `https://updev.local.site` and remotely at `https://dev.unipixelhq.com`.
- DB URL search-replace (`unipixelhq.com` → `updev.local.site`) deferred — Rohan will handle via a WP plugin. **Still outstanding for local DB.**
- Also: uphq repo renamed on GitHub from `up` → `uphq`; local remote updated.

### Major docs refactor

Restructured plugin docs from deeply-nested `plugins/unipixel/.claude/` up to repo-root `.claude/` with a drawing-inspired operating manual.

New structure:
- Root `CLAUDE.md` — operating manual, plugin-focused, with session protocol, autonomy levels, release gate (4-file version bump), decision tree, knowledge herding rule.
- `.claude/app-knowledge/` — `app-knowledge.md` (stack, architecture, hook flow, dev workflow, testing), `deploy-and-release.md` (rsync, _obf, version bumping, wp.org SVN, pre-export checklist).
- `.claude/domain-knowledge/` — `vocabulary.md`, `platform-discoveries.md` (TikTok/Meta audit findings + release quality), `licensing-and-protection.md`, `event-logs.md`.
- `.claude/marketing-knowledge/` — `positioning.md` (what UniPixel is, pillars, industry problem, language rules), `priorities.md`, `campaigns.md` (Google competitor + universal, Meta/Insta, forums, channels), `stape-alternatives.md`.
- `.claude/projects/` — `release-log.md` (replaces backlog-and-changelog.md, with freshness pass fixing stale v2.5.4 marker), `multi-tier-clickid-persistence.md`.
- Plugin folder has a thin breadcrumb `CLAUDE.md` only. Old plugin `.claude/` folder removed.
- `.claude/settings.json` allows `.md` edits under CLAUDE.md + `.claude/**` without prompting.

---

## Where We Need To Go

### IMMEDIATE — Finish local updev setup

- Run the URL search-replace on the local `updev` DB (Rohan's plugin-based method) so internal links resolve to `updev.local.site` instead of prod `unipixelhq.com`.
- Smoke test the local site + admin.

### IMMEDIATE — Deploy v2.6.0 to wordpress.org

Obf export ready, smart-quote check passed last session. Workflow:
1. Pre-export checklist (see `.claude/app-knowledge/deploy-and-release.md` — smart quotes grep, php -l on source).
2. `cd _obf && bash obf.sh export` — writes into SVN working copy at `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`.
3. Lint obfuscated output via filename + stdin (stdin matches wp.org's SVN hook behaviour — caught v2.6.0 stray-quote regression last time).
4. Exclusion check — `.claude/` and `CLAUDE.md` must not be in export.
5. TortoiseSVN commit + tag.

### IMMEDIATE — Monitor Google Ads post-verification

Verification completed 24 March. Expecting impressions within 24-48 hours. Check both campaigns:
- Campaign 3 (Perch and Poach — 6 competitor ad groups including Meta Pixel WordPress)
- Campaign 4 (Universal — Server-Side Tracking, WooCommerce Tracking, Platform-Specific CAPI)
- Remember: "Eligible" ≠ serving. Verify actual impression counts.

### Still on the radar (product)

- **PHP-side validation gap** — `serverside_global_enabled` ON with empty credentials = silent API failures. Add admin notice / validation.
- **Multi-tier click ID persistence** — spec at `.claude/projects/multi-tier-clickid-persistence.md`. High-priority general improvement, Days effort.
- **TikTok expanded event coverage** — AddPaymentInfo first (completes funnel), then CompleteRegistration / SubmitForm for lead-gen verticals.
- **Setup wizard / onboarding flow** — tier-2 priority. Most deactivations happen in first 10 minutes.
- **Custom events wizard** — visual element picker. Current CSS-selector UI unintuitive.
- **Event diagnostics dashboard** — "is it working?" health screen.
- **Stored Event Logs UX** — intro text, filter by event type / platform / date, surface that "Log Server-side Response" must be ON.
- **external_id** — platform upsell metric, not a genuine quality gap for guest-checkout WooCommerce. Assessed and deprioritised; reference `domain-knowledge/platform-discoveries.md` § META-002.
- **Full Pinterest WooCommerce testing** — AddToCart / InitiateCheckout / Purchase still untested on live pipeline.
- **Microsoft CAPI live-endpoint test** — token generation not self-service yet; marked prototype.

### Growth execution

| Priority | Channel | Status | Next action |
|---|---|---|---|
| 1 | Community & forums (Reddit) | Active | 3–5 helpful replies/week |
| 1b | Google Ads | LIVE both campaigns | Monitor impressions post-verification |
| 1c | Meta/Insta Ads | Running | Review creatives and results |
| 2 | YouTube tutorials | Not started | Screen-capture setup walkthroughs |
| 3 | Docs on buildio.dev | Partial | Google setup guide needed |
| 4 | Platform partnerships | Not started | Research partner program requirements |
| 5 | Third-party advocates | Not started | Identify reviewers, agencies |
