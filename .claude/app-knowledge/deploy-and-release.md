# Deploy & Release

Two distinct flows:

1. **Deploy to dev** — rsync from local updev to `dev.unipixelhq.com`. Fast, reversible, internal. Use freely for sanity-checking changes.
2. **Release to users** — obfuscate locally → TortoiseSVN commit to wordpress.org. Slow, public, irreversible once users start auto-updating. Gated.

---

## 1. Deploy to dev.unipixelhq.com (rsync)

### Files involved

| File | Purpose |
|---|---|
| `_rsync/.rsync_all` | Include/exclude rules for rsync |
| `_deploy/deploy_all_TEST.sh` | Dry-run (`-n` flag) — prints what would change, writes nothing |
| `_deploy/deploy_all_LIVE.sh` | Real rsync |

Target: `buildiod@vda4300.is.cc:domains/dev.unipixelhq.com`. Server-side `.env` lives at `~/domains/dev.unipixelhq.com/.env` (not rsync'd — created manually).

### How to run

Run from Ubuntu/WSL for native rsync/ssh (Windows Git Bash has path-mangling and rsync quirks).

```bash
cd /mnt/c/xampp/htdocs/updev/_deploy
bash deploy_all_TEST.sh        # dry-run — review what would change
bash deploy_all_LIVE.sh        # real sync
```

### What ships to dev vs doesn't

Currently pushes: `public_html/***` (everything), `vendor/***`, `composer.json/lock`. Excluded: `.git/`, `.env`, `_deploy/`, `_rsync/`, `_obf/`, `node_modules`, `wp-content/uploads/`, `wp-content/cache/`, log files, `.htaccess`, `php.ini`, `.well-known/`.

The plugin's internal `.claude/` and `CLAUDE.md` **do** get deployed to dev.unipixelhq.com. That's fine — dev is a team-side surface, not end-user distribution. The exclusion that matters (obfuscation export) is handled separately.

---

## 2. Release to wordpress.org

`obf.sh export` writes the obfuscated plugin to the export folder defined in `_obf/obf.sh` (`DEFAULT_EXPORT`: `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`). This is a scratch/staging folder — each run cleans and rewrites it, nothing important is lost.

After export + post-export checks pass, Rohan manually pastes the obfuscated files into the wordpress.org SVN repository's tag folders and commits via TortoiseSVN. The export script itself does not touch SVN.

### The Release Gate — 5 files, always together

Every version bump touches:

1. `public_html/wp-content/plugins/unipixel/unipixel.php` — `* Version: X.Y.Z` header
2. `public_html/wp-content/plugins/unipixel/unipixel.php` — `define('UNIPIXEL_VERSION', 'X.Y.Z');`
3. `public_html/wp-content/plugins/unipixel/readme.txt` — `Stable tag: X.Y.Z` + changelog entry
4. `.claude/projects/release-log.md` — move "Staged for next release" into Released History, update "Current State"
5. **On `unipixelhq.com` (separate site at `C:\xampp\htdocs\uphq\`):** `public_html/wp-content/plugins/unipixelhq-seo/unipixelhq-seo.php` — bump `UPHQ_PLUGIN_VERSION` constant. Drives `softwareVersion` in the `SoftwareApplication` JSON-LD on the marketing site. Without this bump, search engines and AI crawlers see the old version on the marketing site even after wp.org has the new one. Deploy via `cd /c/xampp/htdocs/uphq/_deploy && ./deploy_all_LIVE.sh`. Full context in `.claude/projects/structured-data-implementation.md`.

### Release notes — write the changelog entry, then present for sign-off BEFORE obfuscation

The wp.org changelog entry in `readme.txt` ships to every existing user as the visible diff between versions. It is read by busy store owners, not developers. Get this wrong and the release feels noisy, amateur, or oversharing — even when the code is fine.

**Process — non-negotiable order**:

1. Draft the entry in `readme.txt`.
2. Update `release-log.md` Released History (longer-form internal note, different audience).
3. **Show the proposed `= X.Y.Z =` changelog entry to the user in chat. Stop. Wait for explicit approval or edits.**
4. Only then run `obf.sh export`. Re-running the export burns time and patience — get the wording right first.

**Security rule (top priority)** — fixes for crashes, fatals, auth gaps, injection, or any vulnerability MUST be described in a way that does not disclose:

- the **trigger / attack vector** ("if X happens", "after Y state", "when Z is set")
- the **state required** to reproduce the bug
- the **mechanism of the fix** (what now happens that didn't before)
- the **internal name** of the affected code path or option

Why this matters: every existing install on the previous version is exposed until they auto-update. A descriptive changelog turns the release note into a how-to-attack-old-versions guide, with the patched code as the diff key. The fewer installs that have auto-updated, the more harmful a verbose note is.

Use the **bare-symptom** form. "Fixed - issue with plugin debugging settings causing WordPress error in some scenarios" is enough. The detail belongs in `release-log.md` (internal) and the git commit (internal). Not in `readme.txt`.

If a fix is genuinely too sensitive to describe even at symptom level — a real vulnerability with active risk — coordinate the disclosure separately and say nothing in the changelog beyond "Security and stability fixes."

---

**Voice rules** (apply every release):

- **One short line per change.** Customer-visible only.
- **No mechanism, no internals.** Don't describe how the bug worked, what code path, what the fix does under the hood, or what state the user "could be in." Users do not need reassurance about defaults, fallbacks, restores, or hiccups.
- **Skip cosmetic-only changes that don't matter to a busy store owner** — typos, brand-casing, dev-only logging tweaks. They count as housekeeping, not user-facing news.
- **Match `marketing-knowledge/writing-style.md`** voice. Plain English. No marketing fluff. No emoji. No "we're excited to announce." No "rare edge case." No long compound sentences.
- **Lead with what changed**, not why or how. The user's mental model is "what's different in my admin / on my site?" — answer that.
- **Group by visible surface**, not by code area. Users don't know what `unipixel-enqueue.php` is.

**Anti-pattern (do not do this)**:

```
* Fix: Front-end could fatal if the plugin's logging settings ended up in an unexpected
  state (e.g. after a database hiccup or partial restore). Now safely falls back to
  defaults instead of breaking the page.
* Fix: "TikTok" was rendered as "Tiktok" on the TikTok Setup page header. Brand casing
  now correct.
```

Why bad: explains the inner workings, names internal code paths in user voice, includes a cosmetic typo nobody noticed.

**Good shape**:

```
= 2.6.7 =
* Renamed "Custom events" to "Site events" across the admin for clearer wording.
* Added help icons in the Event Manager.
* Fixed - issue with plugin debugging settings causing WordPress error in some scenarios.
```

Why good: each line is what the user will see / experience, in their language, no internals, casing typo dropped.

**If unsure whether something belongs**: ask "would a store owner with 50 things on their plate want to read this?" If no, it's not for the changelog. Internals go in `release-log.md` Released History only.

---

### Pre-export checklist (mandatory)

These exist because of real shipped-bug incidents. See `domain-knowledge/platform-discoveries.md` § RQ-001 and RQ-002 for the backstory.

1. **Smart quotes scan** — no U+2018/U+2019/U+201C/U+201D in any `.php` file:
   ```bash
   cd C:/xampp/htdocs/updev/public_html/wp-content/plugins/unipixel
   grep -rl $'\xe2\x80\x98\|\xe2\x80\x99\|\xe2\x80\x9c\|\xe2\x80\x9d' --include="*.php" .
   ```
   Must return empty. If not, fix with:
   ```bash
   sed -i "s/\xe2\x80\x98/'/g; s/\xe2\x80\x99/'/g; s/\xe2\x80\x9c/\"/g; s/\xe2\x80\x9d/\"/g" <files>
   ```
2. **`php -l` on source** — lint every PHP file in the plugin folder. Must all be "No syntax errors."
3. **Version check** — `UNIPIXEL_VERSION` in `unipixel.php` matches `Stable tag` in `readme.txt` matches the plugin header `Version:`.

### Run obfuscation

```bash
cd C:/xampp/htdocs/updev/_obf
bash obf.sh dry           # optional — preview
bash obf.sh export        # writes obfuscated files to the scratch export folder
```

Default behaviour:
- Obfuscates to the scratch export folder (path set in `_obf/obf.sh`)
- Applies: `--encode-strings --minify --strip-comments --verbose`
- Loads `exclude-list.txt`:
  - `unipixel.php` (main file — keep clean for WP to read the plugin header)
  - `assets/` (images, static data)
  - `.claude/` (internal docs — never ships to users)
  - `CLAUDE.md`
- Each run cleans and rebuilds the export folder.

### Post-export checks

1. **`php -l` on export (filename + stdin)** — stdin mode matches wordpress.org's SVN pre-commit hook behaviour. The hook runs `php -l` on piped/collapsed content, which catches obfuscation-induced errors (collapsed whitespace, mid-string stray quotes) that filename-mode misses:
   ```bash
   EXPORT="C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports"
   for f in "$EXPORT"/**/*.php "$EXPORT"/*.php; do
     result=$(php -l "$f" 2>&1)
     echo "$result" | grep -qi "error" && ! echo "$result" | grep -q "No syntax errors" && echo "FAIL: $f" && echo "$result"
   done
   # Also spot-check via stdin:
   cat "$EXPORT/functions/unipixel-functions.php" | php -l
   ```
   No FAIL output = good.
2. **Exclusion check** — confirm `.claude/` and `CLAUDE.md` are NOT present in the export folder.

### Paste into SVN, then commit via TortoiseSVN

1. Copy the obfuscated contents from the export folder into the wp.org SVN repository's `tags/X.Y.Z/` folder (create the tag folder if needed). Update `trunk/` too if appropriate.
2. TortoiseSVN → Commit — review the diff, commit with a version-bump message.
3. wordpress.org typically picks up the new `Stable tag` within minutes.

### After release

- Verify the new version appears on wordpress.org/plugins/unipixel/ and the install count / rating / reviews page looks right.
- Update `.claude/projects/release-log.md` — move staged items into Released History with date.
- Spot-check by installing the released version on a test WP somewhere clean.

---

## Release quality — recurring issues

Two bugs have shipped in recent history. Both are preventable with the pre-export checklist above.

- **Smart quotes contamination (v2.5.3)** — 15 PHP files shipped with curly quotes, fatal errors on all WooCommerce events. Fixed v2.5.4.
- **Stray closing quote in multiline PHP string (v2.6.0 work)** — file linted clean via `php -l <filename>` but failed via stdin after obfuscation collapsed whitespace.

Full incident notes in `domain-knowledge/platform-discoveries.md` § Release Quality — Recurring Issues.

---

## Hotfix protocol (if a bad release ships)

1. Identify the bug, reproduce locally.
2. Fix on the source. Run the pre-export checklist.
3. Bump to the next patch version (e.g. if `2.6.3` is broken, hotfix is `2.6.4`).
4. `obf.sh export` → post-export checks.
5. TortoiseSVN commit + tag.
6. Monitor wordpress.org reviews / support forum for downstream complaints from users still on the bad version — they'll auto-update to the hotfix.

No rollback on wordpress.org — old versions stay in the SVN history but you can't un-publish a release that's already been installed.
