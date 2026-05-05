# Custom SEO + GEO Layer for unipixelhq.com

> **Status:** Live. Deployed 2026-05-05.
> **Replaces:** Yoast SEO (deactivated 2026-05-05; data preserved in postmeta).
> **Result:** Fully custom JSON-LD schema, Open Graph, Twitter Cards, meta description, canonical, title-tag, robots-tag, plus `llms.txt` and AI-crawler-allowed `robots.txt`. Zero third-party SEO plugin dependencies. Author identity hard-coded to **UniPixelHQ** across schema and visible bylines.

---

## Why custom

- **Author identity control.** Yoast and Rank Math attribute articles to the WordPress user by default. Every article on `unipixelhq.com` must be attributed to *UniPixelHQ* as the brand. Plugin UI gymnastics make this fragile.
- **Tight SoftwareApplication schema.** The plugin landing schema needs specific fields (`downloadUrl`, `applicationCategory`, `softwareVersion`, eventually `aggregateRating`). Direct PHP gives full control.
- **One self-contained file.** No premium-feature paywalls, no sprawling settings UI, no third-party plugin updates to track.
- **GEO-ready.** AI engines (ChatGPT search, Perplexity, Google AI Overviews, Claude search, Bing Copilot) parse schema.org for entity disambiguation. Custom output gives clean, factual, citation-friendly metadata.

---

## What's deployed

### File: `wp-content/plugins/unipixelhq-seo/unipixelhq-seo.php`

One self-contained plugin. PHP-lint clean. Every concern below handled in a single file.

| Concern | Implementation |
|---|---|
| **JSON-LD schema** | `Organization` (every page), `WebSite` (home), `SoftwareApplication` (home), `BlogPosting` (posts), `Article` (docs pages). Full graph with `@id` cross-references. |
| **Open Graph** | `og:locale`, `og:type`, `og:title`, `og:description`, `og:url`, `og:site_name`, `og:image`, `og:image:width`, `og:image:height`. On posts: `article:published_time`, `article:modified_time`, `article:author`, `article:publisher`. |
| **Twitter Cards** | `summary_large_image` with title, description, image, site, creator. |
| **Meta description** | Yoast `_yoast_wpseo_metadesc` (preserved) → post excerpt → trimmed content → default. Placeholder syntax expanded. |
| **Title tag** | Yoast `_yoast_wpseo_title` (preserved, placeholders expanded) → "Post Title \| Site Name" default. Also removes WP core's `_wp_render_title_tag` to avoid duplicates. |
| **Canonical** | Yoast `_yoast_wpseo_canonical` override (preserved) → permalink. Removes WP core's `rel_canonical` to avoid duplicates. |
| **Robots tag** | Per-page `noindex` from Yoast `_yoast_wpseo_meta-robots-noindex` (preserved). Default: `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`. |
| **Author identity** | Hard-coded as UniPixelHQ. `the_author`, `get_the_author_display_name`, `get_the_author_user_nicename`, `get_the_author_nickname` filters all force the brand name. Schema uses Organization-as-author. |
| **Yoast/Rank Math kill** | Both legacy filters (`wpseo_json_ld_output`, `wpseo_opengraph`, `wpseo_twitter`, `wpseo_metadesc`, `wpseo_canonical`) and Yoast v14+ presenter filters (`wpseo_frontend_presenter_classes`, `wpseo_frontend_presentation`). Same for Rank Math (`rank_math/json_ld`, `rank_math/opengraph/*`, etc.). Defensive — works whether either plugin is active or not. |
| **Sitemap** | WordPress core's `/wp-sitemap.xml` (built in since 5.5). Yoast's old paths (`sitemap_index.xml`, `sitemap.xml`, `post-sitemap.xml`, `page-sitemap.xml`, `category-sitemap.xml`) 301-redirect to core sitemap so any Google-cached references continue to resolve. |
| **WP noise** | `<meta name="generator">` removed. |

### File: `public_html/llms.txt`

Markdown index for AI engines. Lists the plugin (wp.org + home), all docs setup guides, all blog articles, and brand surfaces (GitHub, wp.org, Buildio).

### File: `public_html/robots.txt`

Explicit `Allow: /` for: GPTBot, ChatGPT-User, ClaudeBot, anthropic-ai, Claude-Web, PerplexityBot, Google-Extended, CCBot, cohere-ai, Applebot-Extended, Googlebot, Bingbot, DuckDuckBot. Default `User-agent: *` blocks `/wp-admin/` only. Sitemap line points at `/wp-sitemap.xml`.

---

## Yoast deactivation, data preservation

Yoast was deactivated on 2026-05-05. The plugin reads Yoast's stored data so nothing was lost.

### Data sources read by the plugin

Per-post (from `wp_postmeta`):
- `_yoast_wpseo_title`
- `_yoast_wpseo_metadesc`
- `_yoast_wpseo_opengraph-title`
- `_yoast_wpseo_opengraph-description`
- `_yoast_wpseo_opengraph-image`
- `_yoast_wpseo_twitter-title`
- `_yoast_wpseo_twitter-description`
- `_yoast_wpseo_twitter-image`
- `_yoast_wpseo_canonical`
- `_yoast_wpseo_meta-robots-noindex`

Site-level (from `wpseo_titles` option):
- `title-page`, `metadesc-page` (when home is a static front page)
- `title-home-wpseo`, `metadesc-home-wpseo` (when home is a blog)

### Yoast placeholder expansion

The plugin expands Yoast's title placeholder syntax to plain text:

| Placeholder | Expanded to |
|---|---|
| `%%title%%` | post title |
| `%%sitename%%` | `get_bloginfo('name')` |
| `%%sitedesc%%` | `get_bloginfo('description')` |
| `%%sep%%` | `\|` |
| `%%page%%` | (stripped) |
| `%%excerpt%%` | post excerpt |
| `%%date%%` | post date |
| `%%modified%%` | modified date |
| `%%id%%` | post ID |
| `%%name%%` | author display name |
| `%%currentyear%%` | current year |
| `%%currenttime%%`, `%%currentdate%%` | (stripped) |
| Any other `%%...%%` | stripped (defensive) |

### Do NOT delete Yoast with "remove all data"

Yoast can be safely deactivated or even uninstalled. Do **not** tick the "remove all plugin data" option in Yoast's uninstall flow — that wipes the `_yoast_wpseo_*` postmeta records and breaks our title/description fallthrough. As long as those records remain, the SEO output stays correct.

---

## Validation results (live, post-deactivation)

### Home (`https://unipixelhq.com/`)
- `<title>`: 1 (Yoast-stored value preserved)
- `og:title`: 1
- JSON-LD blocks: 3 (`Organization`, `WebSite`, `SoftwareApplication`)
- Yoast HTML comments: 0

### Blog post (`/i-just-wanted-tracking-so-why-am-i-being-offered-cloud-hosting/`)
- Title: `I just wanted tracking. So why am I being offered cloud hosting? \| UniPixel`
- Meta description: Yoast-stored value preserved
- `og:type`: `article`
- `article:published_time`, `article:author`, `article:publisher` all present
- JSON-LD: `Organization` + `BlogPosting` with `WebPage` + `ImageObject` cross-references

### Docs page (`/unipixel-docs/setting-up-unipixel-with-meta/`)
- Title from postmeta
- JSON-LD: `Organization` + `Article`
- Canonical correct

---

## Per-release maintenance

When a new UniPixel plugin version ships:

**Update `UPHQ_PLUGIN_VERSION`** in `unipixelhq-seo.php` to match the plugin's `Stable Tag` on wp.org. One-line change. Without this, `softwareVersion` in the `SoftwareApplication` schema lags the live version.

This is the fifth release-gate item, captured in `CLAUDE.md` § Release Gate.

Deploy: `cd /c/xampp/htdocs/uphq/_deploy && ./deploy_all_LIVE.sh`.

## Other maintenance

- **`llms.txt`**: update when major site surfaces change (new docs section, navigation overhaul). Otherwise static.
- **`robots.txt`**: update when a new high-priority AI crawler emerges. Currently covers all major ones as of 2026-05.
- **`UPHQ_BRAND_LOGO` / `UPHQ_BRAND_OG_IMAGE`**: update if the logo or share image URL changes.
- **`aggregateRating` schema block**: currently commented out. Uncomment and populate once wp.org has 5+ reviews.

---

## Deploy infrastructure

The marketing site uses `_deploy/deploy_all_LIVE.sh` (rsync over SSH to `buildiod@vda4300.is.cc`).

### Changes made to deploy infrastructure during this implementation

- **`_rsync/.rsync_all`**: whitelisted `+ public_html/wp-content/plugins/unipixelhq-seo/***` BEFORE the `- public_html/wp-content/plugins/**` exclude line. The default rsync config excludes all plugins; new plugins must be opted in by name.
- **`_deploy/deploy_all_LIVE.sh` and `deploy_all_TEST.sh`**: added `-o StrictHostKeyChecking=accept-new` to the SSH transport flag to handle first-connect host key verification automatically. Replaces the older interactive workflow that didn't work cleanly with cwrsync's bundled SSH on Git Bash.

Deploy command:

```bash
cd /c/xampp/htdocs/uphq/_deploy
./deploy_all_TEST.sh   # dry run
./deploy_all_LIVE.sh   # live
```

---

## File map

| Path | Role |
|---|---|
| `C:\xampp\htdocs\uphq\public_html\wp-content\plugins\unipixelhq-seo\unipixelhq-seo.php` | The custom plugin |
| `C:\xampp\htdocs\uphq\public_html\llms.txt` | AI engine index |
| `C:\xampp\htdocs\uphq\public_html\robots.txt` | Crawler allowlist |
| `C:\xampp\htdocs\uphq\_rsync\.rsync_all` | Deploy whitelist (includes new plugin path) |
| `C:\xampp\htdocs\uphq\_deploy\deploy_all_*.sh` | Deploy scripts (now with `accept-new` SSH flag) |

---

## External validation tools

- [Google Rich Results Test](https://search.google.com/test/rich-results?url=https%3A%2F%2Funipixelhq.com%2F)
- [Schema.org Validator](https://validator.schema.org/#url=https%3A%2F%2Funipixelhq.com%2F)
- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/?q=https%3A%2F%2Funipixelhq.com%2F) (click "Scrape Again" to flush FB cache after deploy)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)

---

## Cross-references

- **Authoritative version source for `UPHQ_PLUGIN_VERSION`:** the plugin's `unipixel.php` header in `public_html/wp-content/plugins/unipixel/` (in this `updev` repo).
- **Author-as-brand rule:** documented in `marketing-knowledge/positioning.md` § What UniPixel Is.
- **Release Gate (now five files, not four):** `CLAUDE.md` § Release Gate.
- **GEO context (why structured data matters for AI engines):** discussed inline above; broader content strategy in `marketing-knowledge/unipixelhq-content.md`.
