# Session State — Rohan

> **⚠ Bypass permission mode is ON for this project.** `permissions.defaultMode: bypassPermissions` is set in `.claude/settings.local.json`. Every Claude Code session in this project starts with no permission prompts. Set during the v2.6.6 testing-pass setup (2026-05-03). **Remove the line when testing is done** to restore the safety net for routine work. CLAUDE.md autonomy rules (commit / push / deploy / release / version-bump = explicit instruction only) still apply — the model still follows them; it's only the *harness-level prompt layer* that's off.

---

## Where We Came From

- **Microsoft event-name mismatch** investigation parked. Preferred fix is Option C (make `event_platform_ref` editable per row in the WooCommerce events table). Full writeup: `projects/microsoft-event-name-mismatch.md`. Did **not** ride in 2.6.9.
- The token-acquisition UX initiative (wizards / Test Connection / status strips / home badges / plain-English log labels / client-server split / autofill hardening) was staged on `main` since 2.6.7, plus the 2.6.8 `log_time` index hotfix forward-port.

## What We Worked On

### 2.6.9 release-readiness pass, fixes, build + stage (2026-06-15)

Ran a full browser + DB test pass on updev (and a real-upgrade smoke test on uphq) over the staged batch, fixed three bugs, then bumped + obfuscated + staged 2.6.9. **Source committed & pushed: `origin/main` `3e4c838 "2.6.9 release"`.**

**Fixes shipped in 2.6.9 (all in 3e4c838):**
- **Log prune was completely inert** — `cleanup_logs()` (`classes/class-unipixel-log.php`) passed the ID array as one `wpdb::prepare()` arg, so the DELETE collapsed to an empty query and the event log grew unbounded. Fixed with `...$ids` spread; gauge now set to true `COUNT(*)`; thresholds retuned to 20k trigger / 10k delete.
- **`unipixel_meta` "Ajax request failed" / JSON.parse** (user-reported) — `handler-platform-settings.php` read `$_POST['pageview_send_serverside']` unguarded (the JS never sends it); on hosts with display_errors on, the warning corrupted the AJAX JSON. Guarded.
- **Silent PageView clobber** — same missing field meant every setup-page save rewrote `pageview_send_serverside` to 0, undoing the events-page toggle. Now only written when posted.
- **Google badge honesty** — `page-google-setup.php`: "Server-side ready" → "Format checks passed" + GA4 DebugView note (MP can't verify the API secret).
- **Help-icon popover hover-bridge** — `admin/js/admin-common.js`: popovers hid before the cursor reached their links. 50ms → 250ms hide delay + keep-open handlers bound via `shown.bs.popover`.

**Verified:** upgrade auto-runs on `plugins_loaded` version change, idempotent + lossless (forced 2.6.0→ test, and real 2.6.0→2.6.9 on uphq); prune deletes + gauge mirrors (seeded 20k+); Meta Test Connection live-passed; client-only path fires token-free with non-alarming UI; `platform_enabled` gates client + server firing; all 5 wizards accurate with current deep links. Full obfuscation sweep passed (php -l filename+stdin, node --check on all JS, coverage/parity/zero-byte). Obfuscated 2.6.9 installed + clicked through on uphq — all admin surfaces render clean.

**Logged findings (none blocking):** the 2.6.8 `log_time` index is largely cosmetic for the prune (MySQL ignores it at `LIMIT 10000`; the working prune is the real fix — see app-knowledge); Meta Graph pinned v18.0; consent popup defaults to Spanish on the dev boxes; dev Pinterest token dead (401); per-event WC CAPI matrix untested (no store catalogue).

## Where We Need To Go

- **Ship 2.6.9:** (1) TortoiseSVN commit `tags/2.6.9/` (new) + `trunk/readme.txt` (modified); (2) **gate file #5** — bump `UPHQ_PLUGIN_VERSION` on uphq (`unipixelhq-seo` plugin) + deploy. Then verify on wordpress.org and watch reviews/forum for the JSON.parse reporter.
- **Deferred:** pick the Microsoft event-name solution (Option C); test the per-event WC CAPI matrix once a product/order flow exists on a test store.
- uphq local marketing dev site left on obfuscated 2.6.9 (2.6.0 backup at `%TEMP%\uphq_unipixel_backup_pre269` if revert wanted).

### Memories
None new this session. Existing memories continued to apply (no em dashes, no time estimates, don't project in design conversations, ground strategy in user moments, drafts-are-done).
