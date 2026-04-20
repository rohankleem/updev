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

The obfuscation export folder **IS the wordpress.org SVN working copy**. `obf.sh export` writes directly into that folder; TortoiseSVN commits from it. One mechanism, not two.

Path: `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`.

### The Release Gate — 4 files, always together

Every version bump touches:

1. `public_html/wp-content/plugins/unipixel/unipixel.php` — `* Version: X.Y.Z` header
2. `public_html/wp-content/plugins/unipixel/unipixel.php` — `define('UNIPIXEL_VERSION', 'X.Y.Z');`
3. `public_html/wp-content/plugins/unipixel/readme.txt` — `Stable tag: X.Y.Z` + changelog entry
4. `.claude/projects/release-log.md` — move "Staged for next release" into Released History, update "Current State"

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
bash obf.sh export        # writes into the SVN working copy
```

Default behaviour:
- Obfuscates to `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`
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

### Commit to wordpress.org via TortoiseSVN

From the export/SVN working copy folder:

1. TortoiseSVN → Commit — review the diff, commit to `trunk/` with a version-bump message.
2. Tag the release — TortoiseSVN → Branch/tag... → copy `trunk` → `tags/X.Y.Z`.
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
