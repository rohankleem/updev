# UniPixel HQ — Project Truths

All paths relative to `public_html/wp-content/themes/buildio2/`

## Build

- **Webpack 5** with Babel + Sass compilation
- **Entries**: Bootstrap JS bundle, `src/js/theme-custom.js`, `src/js/main.js`, hs-mega-menu, `src/scss/theme.scss`
- **Output**: `dist/main.bundle.js`, `dist/main.bundle.css`
- **Commands**: `npm run dev`, `npm run prod`, `npm run watch`

## SCSS Architecture

- Entry: `src/scss/theme.scss`
- Chain: Bootstrap functions → custom vars (`user-variables.scss`) → Bootstrap vars → utilities → Swiper → Bootstrap core → custom overrides (`user.scss`)
- 180+ partials in `src/scss/front/` (navbar, buttons, forms, cards, modals, pagination, animations, etc.)
- Design tokens in `src/scss/themes/default.scss`

## Styling Preferences

- User prefers **plain CSS syntax** inside `_user.scss` — avoid SCSS-specific features (nesting, mixins, @extend) unless there's a clear advantage (e.g. using `$primary` variable)
- Keep custom styles readable as flat CSS where possible
- Use SCSS variables when referencing theme tokens (colors, breakpoints) but write rules in straightforward CSS otherwise
- **Prefer Bootstrap utility classes** on elements over writing custom CSS — especially for spacing, responsive behaviour
- Bootstrap 5 has no `xs` prefix — unprefixed classes (e.g. `ps-3`) are the mobile default, override upward with breakpoint prefixes (e.g. `ps-md-0`)

## JavaScript

- `src/js/theme-custom.js` — SVG duotone icon injection (18 icons mapped to container IDs)
- `src/js/main.js` — Swiper carousel init (course hero, blog snippets)
- `src/js/hs.*.js` — HTMLstream UI helper modules (13 files)

## PHP Structure

- `functions.php` — Enqueues dist bundles, SMTP email config (env vars), custom REST endpoint (`/wp-json/custom/v1/monday-webhook`), Bootstrap pagination helper, 8-word excerpt length
- **inc/** — nav-header, hero variants (home, contact, unipixel), blog-snippets, home sections:
  - `inc/home/` — approaches (8 variants), brands (3), cases, outcomes (2), skillsets, what
- **page-templates/** — home-page, contact, unipixel-main, unipixel-doc, unipixel-docs-index, scrapbook-index, subitem-population (+ ChatGPT variants)
- **template-parts/content/** — content, content-single, content-page, content-excerpt, content-none

## Dependencies

- Bootstrap 5.3.1
- Swiper 11.0.5
- hs-mega-menu (vendored in `src/vendor/`)
- Duotone SVG icons (vendored in `src/vendor/duotone-icons/`)

## Products / Sections

- **Unipixel** — product pages + documentation (main, doc, docs-index templates)
- **SubItem** — product pages with ChatGPT integration variants

## Blog Post Styling

- **Template chain**: `single.php` → `template-parts/content/content-single.php` (posts only, pages use `page.php`)
- **Content width**: `.blog-single` wrapper — `max-width: 60rem`, padded `2.5rem` left/right for readable line length
- **Typography**: headings `color: #711fe6`, `font-weight: 600`; paragraphs `line-height: 1.6`, `margin-bottom: 1.4rem`
- **Code blocks**: `.wp-block-code code` — `background: #f9f9f9`, `padding: 0.75rem 1.5rem`, `border-radius: 0.625rem`
- **Post meta**: author hidden, "Uncategorized" category hidden — only display meaningful categories/tags

## Header / Navigation

- **Header style**: `.header-soft` — `border-bottom: 1px solid #ddd`, transparent background (no grey fill). Applied on follow-up pages only, not homepage.
- **Nav padding**: subtle `padding-top` / `padding-bottom` on `.header-soft` (0.35rem) for breathing room
- **Logo mobile padding**: `ps-3 ps-md-0` on `.navbar-brand` — Bootstrap utility classes, padding on mobile removed at `md+`

## Mobile Navigation (Offcanvas)

- **Component**: Bootstrap 5 `offcanvas-end offcanvas-lg` — slides from right, only active below `lg` breakpoint
- **Replaces**: previous `collapse`-based toggle that had no animation
- **ID**: `#navbarOffcanvas`
- **CSS vars** in `_user.scss`:
  - `--bs-offcanvas-width: 95%`
  - `--bs-offcanvas-padding-x: 0.75rem`
  - `--bs-offcanvas-padding-y: 0.75rem`
  - `--bs-offcanvas-transition: transform 0.35s ease-in-out`
- **Animation fix**: `transition: transform 0.35s ease-in-out !important` — required because Windows "Animation effects" off triggers `prefers-reduced-motion: reduce` which Bootstrap uses to disable transitions
- **Overflow protection**: `.offcanvas-body { overflow-x: hidden }` prevents horizontal scroll on mobile swipe
- **Dropdown items**: flex layout — title text truncates with CSS `text-overflow: ellipsis`, badges (`flex-shrink: 0`) stay visible
- **Sub-menu width**: `min-width: 0 !important` overrides inline `14rem` inside offcanvas only, desktop mega menu unaffected

## Notebook Sub-Menu (Offcanvas)

- Dynamically populated via `WP_Query` in `inc/nav-header.php` — latest 9 posts
- Titles rendered as full `get_the_title()` — CSS handles truncation via `.text-truncate` span
- First post gets green "New" badge, rest get blue "Recent" badge
- `sc_get_content_substr()` in `functions.php` — general string truncation utility, no longer used for menu titles

## HS Mega Menu

- Vendored at `src/vendor/hs-mega-menu/`
- Handles desktop dropdown behaviour — `hs-has-sub-menu`, `hs-mega-menu-invoker`, `hs-sub-menu`
- Does NOT handle mobile toggle — that's Bootstrap offcanvas
- Animation: `.hs-sub-menu.animated` / `.hs-mega-menu.animated` — `animation-duration: 300ms`
- Reference implementation: steelchief.com.au uses same library with per-item `data-hs-mega-menu-item-options` (eventType, position, maxWidth) and `nav-link-toggle` class

## SEO / Sitemap

- **Yoast SEO** active — manages sitemap at `https://unipixelhq.com/sitemap_index.xml`
- **Not yet submitted to Google Search Console** — needs setup for unipixelhq.com
- **Sitemaps enabled**: posts, pages only
- **Sitemaps disabled**: authors (was exposing `/author/admin_username/`), categories (was exposing `/category/uncategorized/`), tags (11 thin archive pages)
- **Removed/noindexed**: `/sample-page/` (WP default), `/monday-webhook/` (utility endpoint)
- **URL structure**: posts at root (`/post-name/`), no `/blog/` prefix — no SEO impact either way, Google learns structure from sitemap + internal links, not URL paths
- **OG tags**: not implemented yet, can add later when social accounts are active

## Other

- Text domain: `buildiotheme`
- Two nav menus registered: primary, secondary
- Post formats: aside, gallery, quote, image, video
