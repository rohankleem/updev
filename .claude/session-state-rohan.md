# Session State — Rohan

> **⚠ Bypass permission mode is ON for this project.** `permissions.defaultMode: bypassPermissions` is set in `.claude/settings.local.json`. Every Claude Code session in this project starts with no permission prompts. Set during the v2.6.6 testing-pass setup (2026-05-03). **Remove the line when testing is done** to restore the safety net for routine work. CLAUDE.md autonomy rules (commit / push / deploy / release / version-bump = explicit instruction only) still apply — the model still follows them; it's only the *harness-level prompt layer* that's off.

---

## Where We Came From

- v2.6.6 (Centralised Event Manager) and v2.6.7 (terminology pass) shipped to wp.org.
- Token-acquisition UX initiative defined off the back of Moisés (nuespacios.com) support feedback 2026-05-12. Full plan written into `projects/token-acquisition-ux.md`.
- Phases 1–6 shipped 2026-05-12 (Meta Test Connection, status indicators, wizard, plain-English log labels, 14-day log grace period, help-icon refresh).

---

## What We Worked On

### Token-acquisition UX — Phases 7 + 8 + Meta/Google strengthening — ALL SHIPPED 2026-05-13

The full initiative (Phases 1–8) is now done. Project doc `projects/token-acquisition-ux.md` marked **Status: Complete** with per-phase detail preserved. Release-log `#34` marked **Done (staged)** for the next release. `#1` (setup wizard) and `#8` (empty-token validation) both marked Done-by-subsumption. `#30` (Meta test_event_code field) marked Partly Done — TikTok wizard auto-uses test_event_code, Meta wizard mentions Test Events tab.

**Today's work specifically:**

- **Phase 1 (Meta) strengthened with diagnostic feedback.** Format checks before API call (Pixel ID regex `^\d{14,17}$` with cross-platform hint flagging Google's `G-` prefix and TikTok/Pinterest shapes; Access Token check for `EAA` prefix + ≥100 chars). Scope parsing from debug_token (checks `ads_management` present). Expiry parsing (warns if finite expiry within 7 days; finds 0 for long-lived). New helper `unipixel_diagnose_meta_error()` translates Meta error codes/subcodes (190/460 password-changed, 190/463+467 expired, 190/458 app uninstalled, 190/459 checkpoint, 100 invalid parameter, 200/220 missing permission, 4/10/17 rate limit, 230 perms disabled) into specific actionable user messages. Pixel access call code 100 maps to *"Open Business Settings, select your system user, click Add Assets, and grant access to this Pixel..."* Verified end-to-end against real saved token (got full success with `scopes` array confirmed) + three deliberate failure modes.
- **Phase 7 (Google) strengthened with three-layer check.** Added format check on API Secret (`^[A-Za-z0-9_-]{15,40}$`) catching the false-positive green we'd have given for the bogus saved value. Layered with the existing debug-endpoint validation, plus a NEW production-endpoint event firing with `debug_mode: 1` so the test event appears in GA4 DebugView for self-verification. Success message points the user to GA4 → Admin → DebugView for the specific event name `unipixel_test_connection`. Caught a URL bug at test time: the WebFetch summary said `/_debug_/mp/collect` with underscores but the real endpoint is `/debug/mp/collect`. Lesson recorded.
- **Phase 8 (TikTok / Pinterest / Microsoft) — all three propagated.** Three new handlers, three test-connection JS files, three wizard JS files, three setup-page edits, three help-icon refreshes, plus admin.php hookup for all of them. Each handler researched primary-source first (search + direct curl) before any code. TikTok: `https://business-api.tiktok.com/open_api/v1.3/event/track/` with Access-Token header, auto-generated `test_event_code: UP_TEST_*` landing in Test Events tab, soft-error pattern-matching helper `unipixel_diagnose_tiktok_error()`. Pinterest: TWO-call validation — `/v5/user_account` then `/v5/ad_accounts/{id}` — only platform needing THREE credentials (Tag ID + Ad Account ID + Token). Microsoft: `https://capi.uet.microsoft.com/v1/{tag_id}/events` with Bearer token; structured `error.code/message` JSON body parsed through verbatim for transparency; wizard explicitly acknowledges Microsoft CAPI is gated for many users.
- **Diagnostic philosophy consistent across all five platforms.** Format check → real API call → platform-specific error parsing → actionable diagnostic message. Each failure now lands the user at the specific fix instead of generic "Token problem". Where the platform deliberately doesn't validate (Google API Secret), we send to a verification surface the user can check.

### Reflect-back into permanent docs

- `app-knowledge/app-knowledge.md` — new sections on Test Connection pattern, connection state machine, wizard component structure, 14-day grace period, plain-English event log labels. Two new common-pitfalls bullets (AI-summarised URLs, Bootstrap popover sanitizer).
- `marketing-knowledge/priorities.md` — added Token-Acquisition UX bullet to What's Done. Rewrote § "Onboarding" blocking-adoption section to reflect the substantial reduction.
- `projects/release-log.md` — #34 to Done (staged); #1, #8, #30 to Done-by-subsumption (or Partly Done for #30); Staged for next release section now lists the full Token-Acquisition UX bundle with shipped pieces.

### Moisés follow-up email

Drafted and revised yesterday. Per saved memory, "drafted and revised reply" = done; the email lives in yesterday's conversation log. Rohan hasn't confirmed sending; sending is on him.

### Memories saved this session

None new. Existing memories continued to apply (no em dashes in UniPixel content, no time estimates, ground strategy in user moments, drafts-are-done, don't project in design conversations).

---

## Where We Need To Go

### Release the staged work

Source version is v2.6.7. All Phase 1–8 work is staged for the next release. Rohan picks the version number (likely v2.7.0 given the scope) and runs the standard release-gate process (per CLAUDE.md § Release Gate and `app-knowledge/deploy-and-release.md`):

1. Bump version in `unipixel.php` header + `UNIPIXEL_VERSION` constant
2. Bump `Stable tag` in `readme.txt` + add changelog entry summarising Token-Acquisition UX
3. Stamp the release block in `release-log.md`, move "Staged for next release" into the released history
4. Bump `UPHQ_PLUGIN_VERSION` on unipixelhq.com (separate site at `C:\xampp\htdocs\uphq\`) to keep `softwareVersion` in JSON-LD aligned
5. Pre-export checks (smart quotes scan, `php -l` on source)
6. Obfuscation export, post-export checks, TortoiseSVN commit to wp.org

### Send the Moisés follow-up email

Final draft in yesterday's conversation log. Two paragraphs clarifying that the manual app-creation route Moisés went through isn't the easier path Meta currently recommends, reassuring him his setup works, no action needed. Deliberately omits unshipped features. Send when ready.

### Cache-bust note for the dev install

The Test Connection / wizard JS files added today won't fully bust the dev browser cache until the next version bump (or hard refresh). Rohan saw this with the original Phase 6 popover work and the Phase 8 TikTok wizard — both needed a Ctrl+F5 before the new JS executed. Once version is bumped for release, this becomes a non-issue for everyone.

### Marketing follow-ups from the new capability

- **v2.7.0 announcement blog post** (when release ships) — Token-Acquisition UX as the headline feature for the release.
- **Per-platform docs at unipixelhq.com/unipixel-docs/** can shift focus now that the wizards handle most first-time setup. The Meta and Pinterest docs especially are stale and predate the current platform UIs; refresh worth doing.
- **"How UniPixel tells you what's wrong"** could be a competitive piece — none of PYS / Conversios / Stape do platform-specific diagnostic feedback the way we now do.

### Backlog still on the radar (lower priority than before)

- **Visual element picker** for click/shown triggers (residual after v2.6.6 + token-acquisition UX).
- **Event diagnostics dashboard** (tier 2). Substantially overlapped now by the home dashboard badges + Stored Event Logs plain-English labels, but a dedicated "health" page could still help.
- **Multi-tier click ID persistence** (spec at `.claude/projects/multi-tier-clickid-persistence.md`).
- **TikTok expanded event coverage** — AddPaymentInfo, CompleteRegistration, SubmitForm.
- **Consent popup localization Phase 2/3** (spec at `.claude/projects/consent-popup-i18n.md`).
- **Microsoft CAPI live-endpoint test** — Microsoft Test Connection works against the production endpoint now; the residual "we haven't fully tested production" gap from v2.6.0 is partially addressed by users who now go through the Test Connection flow with a real CAPI token.

### Growth execution

| Priority | Channel | Status |
|---|---|---|
| 1 | Reddit + WP forums | Active — 3-5 helpful replies/week |
| 1b | Google Ads | Live — Campaign 3 (competitor) + Campaign 4 (universal). Lead-gen ad group ready post-v2.6.6. |
| 1c | Meta/Insta Ads | Running — v2.6.6 Pinpoint hooks for Graphic 4. New Token-Acquisition UX hooks worth drafting for next release. |
| 2 | YouTube tutorials | Not started |
| 3 | Blog content | 5 articles live; 3 new candidates queued post-v2.6.6; add the v2.7.0 announcement and "diagnostic feedback" pieces when this ships |
