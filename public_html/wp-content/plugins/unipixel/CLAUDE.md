# UniPixel — CLAUDE.md

## Start Every Session Here

1. Read `C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\session-state.md`
2. Check Active Specs table below — if a spec is listed, read that file too
3. Then start work — do not explore the repo, everything you need is in this file

---

## Project Structure — READ THIS ONCE, NEVER DISCUSS AGAIN

### The git repo vs the plugin folder

The git repo is the **entire WordPress website** — 200k+ files. The plugin is one folder inside it. Claude Code's CWD is set to the plugin folder so that searches, globs, and reads never crawl the full WordPress site.

```
Git root:      C:\xampp\htdocs\website-sheds\
Plugin folder: C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\
```

**Scope rule:** All Glob, Grep, and Read calls must target `C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\`. Never search from `C:\xampp\htdocs\website-sheds\`. The only exception is `C:\xampp\htdocs\website-sheds\_obf\` when explicitly discussed.

### All Claude files — every single one — live in the plugin folder

```
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\CLAUDE.md
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\session-state.md
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\specs\
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\worktrees\
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\marketing.md
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\backlog-and-changelog.md
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\discoveries.md
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\licensing-and-protection.md
C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\test-flow.md
```

### `.claude/` document index

| File | What it contains | When to read |
|---|---|---|
| `session-state.md` | Rolling session continuity — what was worked on, where to go next | Every session start (mandatory) |
| `marketing.md` | Full marketing strategy, competitive landscape, pricing model, content plan, messaging voice, WordPress.org readme guidance | Marketing tasks, content creation, readme updates, pricing discussions |
| `backlog-and-changelog.md` | Feature backlog + "Done since vX.X.X" staging area. When releasing, review the done list, Rohan picks the version number, then update readme/marketing/positioning. | Feature planning, release preparation, "what's been done since last release" |
| `discoveries.md` | Knowledge base of platform-specific bugs, diagnostic findings, and gotchas found during testing (TikTok event ID mismatches, Meta parameter issues, etc.) | Debugging platform issues, investigating event quality reports, auditing tracking accuracy |
| `licensing-and-protection.md` | Pro tier gating strategy, licence validation approaches, obfuscation limitations, anti-circumvention analysis | Commercial/monetisation discussions, building the paywall, licence system design |
| `test-flow.md` | End-to-end checkout test procedure, expected events per platform, event naming reference, test results log | After any release or significant change, when verifying pixel events fire correctly |
| `specs/` | Feature specs for in-progress work (empty when nothing active — see Active Specs table below) | When a spec is listed in the Active Specs table |

Nothing Claude-related exists at the git root `C:\xampp\htdocs\website-sheds\`. Do not create anything there.

### Claude files never reach end users

The plugin is distributed after obfuscation. Claude files are excluded at every stage:

- **Obf export** — `C:\xampp\htdocs\website-sheds\_obf\exclude-list.txt` lists both `.claude` and `CLAUDE.md`. Neither is copied to the export folder.
- **Git** — `C:\xampp\htdocs\website-sheds\.gitignore` line 1: `.claude/`. This pattern matches `.claude/` directories **anywhere** in the repo tree, including inside the plugin folder. `CLAUDE.md` is tracked in git (it's project config, not transient state).
- **Rsync** — `C:\xampp\htdocs\website-sheds\_rsync\.rsync_all` excludes the entire plugin: `- public_html/wp-content/plugins/unipixel/***`. The plugin is never deployed via rsync — only via the obf export.

Any new deployment or packaging process **must** exclude `.claude/` and `CLAUDE.md`.

### Deployment pipeline — how the plugin reaches end users

**Step 1: Development** — edit files directly in `C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\`. No build step. Changes are live immediately under XAMPP at `http://localhost/website-sheds/wp-admin/`.

**Step 2: Obfuscation** — run from `C:\xampp\htdocs\website-sheds\_obf\`:

| Command | What it does |
|---|---|
| `bash obf.sh dry` | Preview — shows what would be obfuscated, changes nothing |
| `bash obf.sh here` | Obfuscate in-place — backs up clean files to `unipixel_clean`, obfuscates the original plugin folder |
| `bash obf.sh restore` | Restore — copies clean files back from `unipixel_clean`, removes the backup |
| `bash obf.sh export` | Export — copies plugin to `C:\Users\RohanKleem\Documents\Rohan\buildio\plugin-unipixel\plugin-obf-exports`, obfuscates the copy. Dev folder untouched. |
| `bash obf.sh export C:\custom\path` | Export to a custom folder |

What the obfuscator does: PHP string literals hex-encoded, JS minified (JShrink), CSS minified, comments stripped. What it skips (via `C:\xampp\htdocs\website-sheds\_obf\exclude-list.txt`): `unipixel.php` (entry point), `assets/`, `.claude/`, `CLAUDE.md`.

Flags applied automatically: `--encode-strings --minify --strip-comments --verbose`.

**Step 3: Distribution** — the obf export folder is the distributable. It is zipped/uploaded for end users via the WordPress plugin submission process.

**Rsync (separate — for the WordPress site, NOT the plugin):** `C:\xampp\htdocs\website-sheds\_rsync\` contains rsync scripts that deploy the wider WordPress site to live/test servers. The plugin is excluded entirely — it only reaches production via the obf export path.

---

## Architecture

### Plugin folder structure

```
unipixel/
├── unipixel.php                          ← entry point; all require_once calls live here
├── config/
│   ├── schema.php                        ← DB table creation (dbDelta)
│   └── activation.php                    ← activate/deactivate hooks
├── classes/
│   ├── class-unipixel-log.php            ← writes events to DB
│   └── class-unipixel-db.php            ← DB helpers
├── functions/
│   ├── unipixel-functions.php            ← shared helpers
│   ├── hooks.php                         ← WP hooks: fbclid capture, order events
│   ├── unipixel-enqueue.php             ← frontend script enqueuing
│   ├── consent.php                       ← consent state management
│   ├── send-server-event.php             ← HTTP calls to platform APIs
│   └── send-server-event-handle-result.php
├── trackers/                             ← one pair of files per platform
│   ├── {platform}-enqueue.php            ← registers pixel JS on the frontend
│   └── {platform}-ajax-listener-send-server.php  ← AJAX → server-side API call
├── woocomm-hook-handling/                ← WooCommerce event pipeline
│   ├── hook-handlers-{event}.php         ← registers WP action hooks
│   ├── get-common-woo-data-{event}.php   ← extracts neutral data from WooCommerce
│   ├── prepare-common-to-platform-{event}.php  ← reshapes data per platform format
│   ├── client-side-localize-{event}.php  ← passes data to JS via wp_localize_script
│   └── client-side-send-{event}.php      ← enqueues JS that fires the pixel event
├── admin/
│   ├── admin.php                         ← bootstrap; enqueues admin assets
│   ├── admin-wpmenu.php                  ← WP admin menu registration
│   ├── handlers/handler-{area}-settings.php  ← AJAX save handlers
│   ├── page-{platform}-setup.php         ← admin page per platform
│   ├── page-{platform}-events.php        ← event config page per platform
│   ├── css/ js/ img/
│   └── vendor/fontawesome/
├── js/                                   ← frontend tracking scripts (no build step)
│   ├── unipixel-common.js                ← shared AJAX logging util
│   ├── pixel-{platform}.js               ← platform pixel initialisation
│   ├── clientfirst-watch-and-send-{platform}.js  ← client-side event listeners
│   └── unipixel-consent.js / unipixel-consent-popup.js
└── css/
    └── unipixel-consent-popup.css
```

### Event flow — two distinct patterns

#### Pattern 1: Server-First (WooCommerce events)

Used for: Purchase, AddToCart, Checkout, ViewContent — triggered by WooCommerce PHP hooks, not user clicks.

PHP fires the server-side API call immediately during page build, then injects the client-side pixel as an inline script. When the page renders in the browser, the JS pixel fires automatically — no AJAX callback needed.

```
woocommerce_thankyou hook (PHP)
  → hook-handlers-{event}.php
  → get-common-woo-data-{event}.php          extract order data from WooCommerce
  → prepare-common-to-platform-{event}.php   reshape per platform API format
  → unipixel_send_server_event_{platform}()  HTTP call to Meta/TikTok/Google API ← server fires HERE
  → client-side-localize-{event}.php         wp_localize_script() — bake data into page as JS variable
  → client-side-send-{event}.php             wp_add_inline_script() — inject fbq()/ttq.track()/gtag() call

Browser renders page
  → inline JS reads localized variable (e.g. UniPixelPurchaseMeta)
  → fbq('track', 'Purchase', payload, { eventID })  ← client fires HERE
```

Files involved: `woocomm-hook-handling/` (all 5 pipeline files per event)

---

#### Pattern 2: Client-First (custom click/interaction events)

Used for: PageView, custom button clicks, any event driven by user action in the browser.

PHP can't know when a user clicks something, so JS takes the lead. The browser pixel fires first, then JS calls back to PHP via AJAX to trigger the server-side API call.

```
Page loads (PHP)
  → trackers/{platform}-enqueue.php
  → wp_enqueue_script('clientfirst-watch-and-send-{platform}.js')
  → wp_localize_script() — passes event list, ajaxurl, nonce to JS

User action in browser (JS)
  → clientfirst-watch-and-send-{platform}.js detects trigger
  → fbq('track', 'PageView')                ← client fires FIRST
  → fetch(ajaxurl, { action, event_id, nonce, pageUrl })  ← AJAX to WordPress

WordPress AJAX (PHP)
  → trackers/{platform}-ajax-listener-send-server.php
  → check_ajax_referer()                    verify nonce
  → unipixel_send_server_event_{platform}() HTTP call to platform API ← server fires HERE
```

Files involved: `trackers/{platform}-enqueue.php`, `trackers/{platform}-ajax-listener-send-server.php`, `js/clientfirst-watch-and-send-{platform}.js`

---

#### Deduplication

Both patterns pass the same `event_id` to the server API call and the client pixel. Platforms (Meta, TikTok, Google) use `event_id` to deduplicate — if they receive both, they count it as one conversion, not two. This is intentional.

`unipixel_handle_send_event_result()` logs whether a send was `"serverFirst"` or `"clientSecond"` to track the order in the audit log.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 7.0+ / JavaScript (ES5/6, no transpile) |
| CMS | WordPress 5.0+ |
| Ecommerce | WooCommerce |
| Admin UI | Bootstrap 4/5 + Font Awesome 6.7.2 |
| Database | MySQL via `$wpdb` |
| Schema mgmt | `dbDelta` |
| Server | XAMPP (local), Apache |

---

---

## Coding Patterns

### Adding a new platform

1. `trackers/{platform}-enqueue.php` — register pixel JS via `wp_enqueue_script`
2. `trackers/{platform}-ajax-listener-send-server.php` — handle AJAX → server API call
3. `admin/page-{platform}-setup.php` and `admin/page-{platform}-events.php`
4. `admin/handlers/handler-platform-settings.php` — AJAX save handler
5. Add platform row to `unipixel_platform_settings` in `config/schema.php` with a fixed `id`
6. Add all `require_once` calls to `unipixel.php`

### Adding a WooCommerce event

Follow the 5-file pipeline in `woocomm-hook-handling/`:
1. `hook-handlers-{event}.php`
2. `get-common-woo-data-{event}.php`
3. `prepare-common-to-platform-{event}.php`
4. `client-side-localize-{event}.php`
5. `client-side-send-{event}.php`

### Admin AJAX

- All settings saves go through `admin-ajax.php`
- Actions registered as `wp_ajax_unipixel_{action_name}`
- Always verify nonces in handlers

### Schema updates

- Bump `UNIPIXEL_VERSION` constant in `unipixel.php`
- Edit `unipixel_update_schema()` in `config/schema.php`
- `plugins_loaded` → `unipixel_check_version()` → runs schema update on version mismatch

---

## Security & Coding Standards

These are non-negotiable rules. Every handler, form, and query must follow them.

### 1. Sanitise input — always `wp_unslash()` first, then sanitise

```php
$pixel_id          = sanitize_text_field(wp_unslash($_POST['pixel_id']));
$description       = sanitize_textarea_field(wp_unslash($_POST['description']));
$email             = sanitize_email(wp_unslash($_POST['email']));
$key               = sanitize_key(wp_unslash($_POST['setting_key']));
$platform_id       = intval($_POST['platform_id']);  // or absint() for unsigned
```

For arrays, sanitise each item individually — never sanitise an array as a whole.

Validate where needed: `is_email()`, `is_numeric()`, `filter_var()`.

### 2. Escape output — escape at the point of echo, not before storing

```php
echo esc_html($value);        // plain text
echo esc_attr($value);        // inside HTML attributes
echo esc_url($url);           // URLs in href/src
echo esc_textarea($value);    // inside <textarea>
wp_json_encode($array);       // safe JSON output
```

### 3. Database queries — always prepared

```php
// SELECT
global $wpdb;
$table = $wpdb->prefix . 'unipixel_platform_settings';
$row = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM %i WHERE platform_name = %s", $table, $name),
    ARRAY_A
);

// INSERT (wpdb->insert handles escaping via format array)
$wpdb->insert(
    $wpdb->prefix . 'unipixel_events_settings',
    array(
        'platform_id'       => $platform_id,
        'element_ref'       => $element_ref,
        'event_trigger'     => $event_trigger,
        'event_name'        => $event_name,
        'event_description' => $event_description,
    ),
    array('%d', '%s', '%s', '%s', '%s')
);

// UPDATE — same pattern
$wpdb->update($table, $data_array, $where_array, $data_formats, $where_formats);
```

Never concatenate `$_POST` or user values directly into a query string.

### 4. Nonces & capability — every AJAX handler needs both

```php
// Verify nonce first — dies automatically if invalid
check_ajax_referer('unipixel_ajax_nonce', 'nonce');

// Then check capability
if (!current_user_can('manage_options')) {
    wp_send_json_error('Unauthorized', 403);
}
```

For forms (non-AJAX): use `check_admin_referer()`.

### 5. Enqueue scripts and styles — never inline or hardcoded

```php
wp_register_script('handle', $src, $deps, $version, true);
wp_enqueue_script('handle');
wp_add_inline_script('handle', $inline_js);   // for inline JS tied to a handle

wp_register_style('handle', $src, $deps, $version);
wp_enqueue_style('handle');
wp_add_inline_style('handle', $inline_css);
```

Only enqueue on UniPixel admin pages — check `$hook` before enqueuing.

### 6. Code preservation rules

- Do not remove comments or functions unless explicitly instructed.
- Do not alter existing logic outside the scope of what was asked.
- Prefix all new functions with `unipixel_` — no exceptions.

---

## Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| PHP files | kebab-case | `send-server-event.php` |
| PHP functions | `unipixel_` prefix + snake_case | `unipixel_metric_log()` |
| DB tables | `unipixel_` prefix | `unipixel_event_log` |
| JS files | kebab-case | `unipixel-common.js` |
| WP option keys | `unipixel_` prefix | `unipixel_consent_settings` |
| Admin page hooks | `unipixel_page_` prefix | `unipixel_page_setup_meta` |
| AJAX actions | `unipixel_` prefix | `wp_ajax_unipixel_save_settings` |

---

## Database Tables

All tables use `$wpdb->prefix` (default: `wp_`).

| Table | Purpose |
|---|---|
| `unipixel_platform_settings` | Pixel IDs, access tokens, enabled flag per platform |
| `unipixel_events_settings` | Custom click/interaction events |
| `unipixel_woocomm_event_settings` | WooCommerce event config per platform |
| `unipixel_event_log` | Audit log of all sent events |
| `unipixel_log_count` | Running event count gauge |

---

## Gotchas

1. **dbDelta does not drop columns, rename columns, or change primary keys.** Handle those manually with raw SQL.
2. **Admin assets only load on UniPixel pages.** The check is: `$hook === 'toplevel_page_unipixel'` or `strpos($hook, 'unipixel_page_') !== false`.
3. **The consent popup only loads under two conditions:** `consent_honour = 1` AND `consent_ui = 'unipixel'` in `unipixel_consent_settings`. Both must be true.
4. **`register_activation_hook` is called twice in `unipixel.php`** (once in the main body, once inside the function). The duplicate is harmless but worth knowing.
5. **Google only deduplicates the Purchase event.** For all other Google events, GA4 counts client and server as two separate events. This is a known GA4 limitation — gracefully handled in the admin UI, where the client/server toggles for each event are designed so only one can be active at a time.

---

## Platform Integrations

### Meta (Platform ID: 1)
- **API:** Meta Conversions API (CAPI)
- **Endpoint:** `https://graph.facebook.com/v14.0/{pixel_id}/events`
- **Auth:** Access token in request body
- **Credentials:** Pixel ID + Access Token
- **Client pixel:** `fbq()`
- **User data sent server-side:** IP, user agent, `_fbp` cookie, `_fbc` (fbclid)
- **Patterns:** Server-first (WooCommerce) + client-first (PageView/custom)
- **Deduplication:** Full — uses `event_id` across all events
- **⚠️ Note:** Currently using Graph API `v14.0` — this is old and may need updating

### Google (Platform ID: 4)
- **API:** GA4 Measurement Protocol
- **Endpoint:** `https://www.google-analytics.com/mp/collect?measurement_id={id}&api_secret={secret}`
- **Auth:** API secret as URL query parameter
- **Credentials:** Measurement ID (stored in `pixel_id`) + API Secret (stored in `access_token`)
- **Client pixel:** `gtag()` with fallback to `dataLayer.push()` for GTM
- **Patterns:** Server-first (WooCommerce) + client-first (PageView/custom)
- **Deduplication:** Purchase event only — GA4 Measurement Protocol deduplicates using `event_id` for Purchase. For all other events, GA4 treats client and server as separate and counts both. This is a GA4 platform limitation, handled gracefully in the admin UI with per-event client/server toggles that guide the user to only enable one side.
- **Extras:** Debug mode flag (`debug_mode: true`) for GA4 DebugView, toggleable per environment

### TikTok (Platform ID: 3)
- **API:** TikTok Events API
- **Endpoint:** `https://business-api.tiktok.com/open_api/v1.3/event/track/`
- **Auth:** Access token in `Access-Token` request header (not the body — different to Meta)
- **Credentials:** Pixel ID + Access Token
- **Client pixel:** `ttq.track()`
- **User data sent:** includes `ttp` cookie and `ttclid`
- **Patterns:** Server-first (WooCommerce) + client-first (custom)
- **Deduplication:** Full — uses `event_id`
- **⚠️ Note:** Requires timestamps as 10-digit Unix seconds. Plugin normalises: if value > 10 digits (milliseconds), it divides by 1000

### Pinterest (Platform ID: 2)
- **API:** Pinterest Conversions API
- **Endpoint:** `https://api.pinterest.com/v5/ad_accounts/{ad_account_id}/events`
- **Auth:** Bearer token in `Authorization` header
- **Credentials:** Tag ID (`pixel_id`) + Ad Account ID (`additional_id`) + Access Token (`access_token`)
- **Client pixel:** `pintrk()` from `https://s.pinimg.com/ct/core.js`
- **User data sent server-side:** IP, user agent, `_epik` cookie (click ID), Advanced Matching fields (array-wrapped)
- **Patterns:** Server-first (WooCommerce) + client-first (PageView/custom)
- **Deduplication:** Full — uses `event_id` across all events
- **⚠️ Note:** Pinterest uses different event names: Purchase = `checkout`, AddToCart = `add_to_cart`, InitiateCheckout = `initiate_checkout`, ViewContent = `view_content`, PageView = `page_visit`. Client pixel names are lowercase no-underscore variants (e.g. `addtocart`, `pagevisit`).

### Microsoft (Platform ID: 5)
- **API:** Microsoft Conversions API (CAPI) + UET client pixel
- **CAPI Endpoint:** `https://capi.uet.microsoft.com/v1/{tagID}/events`
- **Auth:** Bearer token in `Authorization` header
- **Credentials:** Tag ID (stored in `pixel_id`) + CAPI Access Token (stored in `access_token`)
- **Client pixel:** UET tag (`uetq.push`)
- **Patterns:** Server-first (WooCommerce) + client-first (PageView/custom)
- **Deduplication:** Full — uses `eventId` across all events
- **Click ID:** `msclkid` captured from URL params, stored in cookie (90-day retention), sent via `userData.msclkid`
- **Consent:** `adStorageConsent` field — `"G"` (granted) or `"D"` (denied), mapped from UniPixel consent state. Client-side consent set via UET consent API before tag init.
- **User data sent server-side:** IP, user agent, msclkid cookie
- **Event name mapping:** Purchase → `"purchase"`, AddToCart → `"add_to_cart"`, Checkout → `"begin_checkout"`, ViewContent → `"view_item"`, PageView → `"pageLoad"` (eventType)
- **CAPI payload structure:** `eventType` ("pageLoad" or "custom"), `eventName`, `eventTime` (unix seconds), `userData`, `customData` (with `ecommTotalValue`, `currencyCode`, `itemIds[]`)
- **⚠️ CAPI prototype status:** Microsoft CAPI requires OAuth token generation that is complex and not yet self-service for all advertisers. The code is fully implemented but CAPI server-side sending is untested against the live endpoint. Client-side UET tracking is confirmed working.
- **⚠️ Note:** Emails and phone numbers are hashed client-side in JS before sending — PHP handler receives them pre-hashed

### Platform comparison

| | Meta | Pinterest | TikTok | Google | Microsoft |
|---|---|---|---|---|---|
| Platform ID | 1 | 2 | 3 | 4 | 5 |
| Auth location | Body | Header (Bearer) | Header | URL param | Header (Bearer) |
| WooCommerce pipeline | ✅ | ✅ | ✅ | ✅ | ✅ (CAPI prototype) |
| Deduplication | All events | All events | All events | Purchase only | All events |
| PII hashing | Server raw | Server raw (array-wrapped) | Server raw | N/A (GA4 MP no PII) | Pre-hashed client-side |
| Advanced Matching | ✅ All 8 fields | ✅ All 8 fields | ✅ All 8 fields | ❌ | ❌ |

---

## Preferences & Rules

### CLAUDE.md Update Protocol

When something worth capturing is discovered, Claude asks: **"Should I add this to CLAUDE.md?"** — and waits for confirmation before writing anything.

### Scope & Commits

- Only change what was explicitly asked.
- Never commit without explicit instruction.
- Suggest freely, act only when asked.
- Do not touch files outside `C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\`.

### Worktrees & VS Code Repositories

#### What worktrees are

Claude Code can create **worktrees** — separate working copies of the git repo. Each one:
- Gets a random name (e.g. `pedantic-taussig`, `nice-dewdney`)
- Lives in `.claude/worktrees/<name>/`
- Gets a branch called `claude/<name>`
- Appears as a **separate repository in VS Code's Source Control sidebar** with its own commit button and changes list

They are created by: `claude --worktree`, `isolation: "worktree"` on Task agents, or `EnterWorktree` tool.

#### The problems

1. **Accumulation:** A new random-named worktree can be created each session. They persist if they contain changes. Nobody cleans them up. VS Code sidebar fills with stale repos.
2. **Repo size:** Git worktrees copy the **entire git repo**, not just the working folder. Because the git repo is the whole WordPress site (200k+ files) but the work scope is only the plugin folder, each worktree duplicates everything. There is no such thing as a partial worktree — this is a git limitation, not a Claude Code limitation.

#### Rules for this project

- Do not use `isolation: "worktree"` when spawning Task agents.
- Do not use the `EnterWorktree` tool.
- At session start, if stale worktrees or `claude/*` branches exist from a previous session, clean them up: `git worktree prune` then `git branch -d claude/<name>`.

#### Future

The worktree concept is useful — the problem is the 200k file duplication. If the plugin ever gets its own git repo, or if git sparse-checkout worktrees prove viable (only checking out the plugin folder), worktrees should be revisited. When that happens: use **one worktree with a fixed name**, clean it at session start, never accumulate multiple.

### Session Continuity

At the start of every session, Claude reads `C:\xampp\htdocs\website-sheds\public_html\wp-content\plugins\unipixel\.claude\session-state.md`.
When told **"update session state"**, Claude rewrites it using the rolling format.

### Active Specs

| Spec | Feature | Status |
|---|---|---|
| _(none yet)_ | | |

> When a feature ships — delete its spec file and remove it from this table.
