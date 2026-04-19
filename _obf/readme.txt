# WP In-place Obfuscator — README

Obfuscates a WordPress plugin's PHP, JS, and CSS files.

---

## Commands

Run from PowerShell, inside the `_obf/` folder. Always prefix with `bash`.

| Command                              | What it does                                                        |
|--------------------------------------|---------------------------------------------------------------------|
| `bash obf.sh dry`                    | Preview only — shows what would happen, changes nothing             |
| `bash obf.sh here`                   | Obfuscate in-place — backs up changed files, obfuscates original    |
| `bash obf.sh restore`                | Restore — copies clean files back over obfuscated ones              |
| `bash obf.sh export`                 | Export — copies plugin to default export folder, obfuscates the copy |
| `bash obf.sh export C:/custom/path`  | Export to a custom folder instead of the default                    |

Default export path: `C:/Users/RohanKleem/Documents/Rohan/buildio/plugin-unipixel/plugin-obf-exports`

The wrapper automatically:
- Finds the plugin folder (`public_html/wp-content/plugins/unipixel`)
- Applies standard flags: `--encode-strings --minify --strip-comments --verbose`
- Loads `exclude-list.txt` (skips `unipixel.php`, `assets/`, `.claude/`)
- Finds PHP (XAMPP) regardless of whether bash is Git Bash or WSL

---

## In-place workflow (`bash obf.sh here`)

1. Deactivate the plugin in WP admin (recommended).
2. Run `bash obf.sh dry` to preview.
3. Run `bash obf.sh here` — backs up changed files to `unipixel_clean`, obfuscates the original in-place.
4. Reactivate plugin and test.
5. To restore: `bash obf.sh restore` — copies clean files back, removes the backup.

## Export workflow (`bash obf.sh export`)

Best for quick test builds where code follows proven patterns.

1. Run `bash obf.sh export` — copies plugin to the export folder, obfuscates the copy.
2. Dev folder is completely untouched — no backup/restore needed.
3. Deploy or test from the export folder.
4. Re-run anytime — the export folder is cleaned and rebuilt each time.

---

## What gets obfuscated

- PHP: string literals hex-encoded (`\xNN`), comments stripped, whitespace minified
- JS: minified via JShrink
- CSS: minified (whitespace + comment removal)

Strings that are NOT encoded (kept safe):
- WordPress hook names, option keys, AJAX actions, REST routes
- Gettext / translation strings
- SQL queries, file paths, URLs
- Array keys, default parameter values
- Serialized strings (`a:...`)

---

## Direct PHP usage (advanced)

If you need to run the PHP script directly instead of through the wrapper:

```
php wp_obfuscator_inplace.php C:/path/to/plugins/unipixel --encode-strings --minify --strip-comments --verbose
php wp_obfuscator_inplace.php C:/path/to/plugins/unipixel --export-to=C:/path/to/output --encode-strings --minify --strip-comments --verbose
php wp_obfuscator_inplace.php C:/path/to/plugins/unipixel --encode-strings --minify --strip-comments --dry-run
```

## All flags (PHP script)

| Flag                    | Description                                                              |
|-------------------------|--------------------------------------------------------------------------|
| `--encode-strings`      | Hex-encode safe string literals to `\xNN` sequences                      |
| `--minify`              | Remove extra whitespace/newlines                                         |
| `--strip-comments`      | Remove comments and PHPDoc blocks                                        |
| `--rename-identifiers`  | Rename local functions/classes/variables (risky — test thoroughly)        |
| `--dry-run`             | Preview only, no files changed                                           |
| `--exclude=a,b,c`       | Comma-separated file/folder names to skip                                |
| `--exclude-list=path`   | Path to a text file listing exclusions (one per line)                    |
| `--salt=string`         | Salt for rename hashing                                                  |
| `--verbose`             | Print file-level progress messages                                       |
| `--export-to=path`      | Copy plugin to this path and obfuscate there (original untouched)        |

---

## Important notes

- Always run `bash obf.sh dry` first if in doubt.
- Test activation, admin pages, AJAX endpoints, REST routes, WooCommerce flows after obfuscating.
- Flush OPcache / restart PHP-FPM after deploying obfuscated builds.
- Keep `obfuscation_map.json` secure if `--rename-identifiers` is used.
