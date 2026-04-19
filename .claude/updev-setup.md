# `updev` — plugin development base

## Goal

Set up `updev` as the dev site where unipixel plugin development, testing, research, and new features happen. Locally: `updev.local.site`. Remotely (later): `dev.unipixelhq.com`.

`uphq` is the public marketing site only. `website-sheds` currently hosts the plugin source but will no longer be needed once `updev` is running.

## Architecture facts

### Local server

- **Laragon is the web server** (not XAMPP's Apache directly). Laragon uses XAMPP's `htdocs` path but its own vhost config at `C:\laragon\etc\apache2\sites-enabled\`.
- Site naming: `<name>.local.site` on port 443, SSL via `C:/laragon/etc/ssl/laragon.crt`.
- `updev.local.site` vhost **already exists** at `C:\laragon\etc\apache2\sites-enabled\updev.local.site.conf`, pointing at `C:/xampp/htdocs/updev/public_html`.
- Hosts entry `127.0.0.1  updev.local.site` **already in** `C:\Windows\System32\drivers\etc\hosts`.
- Only missing at the server level: the `C:\xampp\htdocs\updev\` folder + its DB.

### `uphq` (marketing site — source to copy the WP install from)

- `.env`-driven config: DB name, WP_HOME, table prefix `wx4gk_`, SMTP, Monday API token, etc.
- Git repo: `github.com/rohankleem/up.git` — **to be renamed to `uphq`** on GitHub.
- Whole-site-in-git pattern: ~7762 tracked files including WP core.
- `.gitignore` excludes `.env`, `vendor/*`, `uploads/`, `node_modules`, `*.log`, `composer.lock`, and `plugins/unipixel/*`.
- `_deploy/deploy_all_LIVE.sh` → rsyncs to `unipixelhq.com` (`vda4300.is.cc`).

### `website-sheds` (where plugin currently lives — bits to bring over)

- **Unobfuscated unipixel plugin source** at `public_html/wp-content/plugins/unipixel/` (~4.5M).
- `_obf/` — the obfuscation toolchain: `obf.sh`, `wp_obfuscator_{inplace,css,js}.php`, JShrink, `exclude-list.txt`.
- `obf.sh` commands: `dry`, `here` (in-place + backup), `restore`, `export` (default or custom path).
- Default export path: `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`.
- The project-root `.claude/` in website-sheds is empty — nothing to carry over at that level.

### Plugin-level docs (travel with the plugin, not the site)

The unipixel plugin carries its own `.claude/` folder and `CLAUDE.md` — ~240K of real ongoing knowledge:

- `backlog-and-changelog.md` — release tracking (v2.5.4 live, unreleased work staged)
- `discoveries.md` — cross-session audit findings (e.g. TikTok event-quality investigation)
- `feature-multi-tier-clickid-persistence.md` — feature spec
- `licensing-and-protection.md`, `marketing.md`, `session-state.md`, `test-flow.md`, `content-stored-event-logs-guide.md`, `stape-alternatives-article.md`
- `specs/`, `worktrees/` — scaffolded subdirs
- `settings.local.json`

**Because it lives inside the plugin folder, it travels with the plugin source whenever the plugin folder is copied.** When we copy `website-sheds/public_html/wp-content/plugins/unipixel/` → `updev/...`, the docs come along for free. No separate migration.

### Protecting plugin docs (where exclusion happens)

Plugin-level `.claude/` and `CLAUDE.md` must never ship to end users or to remote servers. Three exclusion surfaces:

1. **Obfuscation export** — `_obf/exclude-list.txt` already excludes `.claude` and `CLAUDE.md` ✓ (output to `...\plugin-obf-exports` is clean).
2. **uphq rsync deploy** — `uphq/_rsync/.rsync_all` skips the whole `plugins/unipixel/**` anyway (marketing site installs the plugin manually from the distribution site), so docs can't leak via this path ✓.
3. **updev rsync deploy (future)** — updev's whole job is deploying unipixel, so its `.rsync_all` must explicitly exclude `plugins/unipixel/.claude/` and `plugins/unipixel/CLAUDE.md`. This is the one new exclusion surface introduced by updev.

### Plugin distribution flow (how unipixel reaches users)

1. Develop plugin source in `website-sheds/public_html/wp-content/plugins/unipixel/`
2. `_obf export` → obfuscated copy at `...\plugin-obf-exports`
3. Upload the obfuscated copy to a WP distribution site
4. End users download the plugin from that WP site
5. `uphq`'s `plugins/unipixel/` is just a third-party-installed copy of the distributed plugin — unrelated to dev

## Steps to set up `updev`

### 1. Copy files

From `uphq/` → `updev/`:
- `public_html/` (full WP site)
- `.env` (then edit), `.env.example`, `.gitignore`, `CLAUDE.md`, `.claude/`
- `composer.json`, `composer.lock`, `package-lock.json`
- `_deploy/`, `_rsync/`
- **Skip**: `.git/`, `vendor/`, `wp-content/themes/buildio2/node_modules/`, `wp-content/uploads/`, `*.log`, `_ignore*`
- **Also skip**: `public_html/wp-content/plugins/unipixel/` — that's the obfuscated distributed build in uphq. The real source comes from website-sheds in step 6.

### 2. Database

- Create MySQL DB `updev`.
- Import a dump of the `uphq` DB into it.

### 3. Rewrite `updev/.env`

- `DB_NAME="updev"`
- `WP_HOME` / `WP_SITEURL` → `https://updev.local.site`
- `WP_DEBUG_LOG="debug_updev.log"`
- **Blank or replace** `SMTP_*` (avoid dev sending real email from `website@unipixelhq.com`)
- **Blank or replace** `MONDAY_API_TOKEN` (avoid dev writing to the real Monday board)

### 4. Fix URLs in the DB

From `updev/public_html/`:
```
wp search-replace https://uphq.local.site https://updev.local.site
```
Handles serialized data. Truncate iThemes Security lockout + transient tables afterwards.

### 5. Dependencies

- `composer install` in `updev/`
- `npm install` in `public_html/wp-content/themes/buildio2/`

### 6. Bring plugin source over

- Copy `website-sheds/_obf/` → `updev/_obf/`
- Copy `website-sheds/public_html/wp-content/plugins/unipixel/` → `updev/public_html/wp-content/plugins/unipixel/` (step 1 skipped uphq's obfuscated copy, so this is the only version in updev — the unobfuscated source + its `.claude/` docs).
- Remove `public_html/wp-content/plugins/unipixel/*` from `updev/.gitignore` — source must be versioned now. Plugin's internal `.claude/` and `CLAUDE.md` get versioned too (the plugin repo is where they belong).

### 7. Git

- On GitHub: rename repo `up` → `uphq`. Locally in `uphq/`: `git remote set-url origin https://github.com/rohankleem/uphq.git`.
- Create new GitHub repo `updev`. In `updev/`: `git init`, add remote, initial commit, push.

### 8. Remote deploy target (later)

- Configure `dev.unipixelhq.com` DNS.
- Provision server, deploy `updev`.
- Add the updev target to `_deploy/` (adapt `deploy_all_TEST.sh`).
- **Update `_rsync/.rsync_all`** so it excludes `plugins/unipixel/.claude/` and `plugins/unipixel/CLAUDE.md` — updev's deploy is the one path that would otherwise leak plugin docs to the remote server.

## Decisions made

- **Plugin source location**: stays at `public_html/wp-content/plugins/unipixel/`, matching the current website-sheds workflow. Tracked in git — updev IS the dev repo, source must be versioned.
- **updev is a new separate repo**, not a branch of uphq. Different purpose, no sync needed.
- **updev's DB starts as a clone of uphq's** — fastest path to "WP that works". Sanitized `.env` keeps it disconnected from real SMTP and the real Monday board.

## Open questions

- Does plugin testing need WooCommerce? (uphq may already have it — will inherit via DB clone.)
- Dev email capture: Mailpit / MailHog, or just blank `SMTP_*`?
- Long term: should plugin source move to `src/unipixel/` with obf building into `plugins/unipixel/`? Deferred until the simple layout shows pain.
