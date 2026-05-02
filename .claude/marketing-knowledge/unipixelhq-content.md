# unipixelhq.com — Content & Structure

What's published at the marketing site. Site role in the funnel, content inventory, what each surface is for. Update when articles ship or the navigation changes.

For voice / editorial rules see `writing-style.md`. For positioning and pillars see `positioning.md`.

---

## Site role

`unipixelhq.com` is the **product marketing site** — where someone who's heard of UniPixel goes to learn, evaluate, and decide whether to install. It's the destination Google search ads land on, where wp.org listing visitors click through to "more info", and where blog content for SEO lives.

It is NOT the WordPress site that runs the plugin (that's separate). It's a public-facing WordPress install at `unipixelhq.com` whose only job is converting interested visitors into installs.

### Related public surface: `github.com/unipixelhq`

A separate brand surface, not part of this site. Different role: the GitHub presence is a discovery and trust hub that links *into* unipixelhq.com (and into wp.org). README, topic tags, releases. No plugin code lives there. See `projects/github-info-repo.md` for full context. Mentioned here so future doc-writing remembers there are now two indexable brand surfaces, not one.

---

## Top-level structure

| Surface | URL | Job |
|---|---|---|
| Home | `/` | Hero + features summary. Convert top-of-funnel arrivals into "I get it, give me docs / install". |
| Documentation | `/unipixel-docs/` | Setup help + feature explanation. Reduces install friction; helps existing users get value. |
| Blog | `/blog/` | SEO-driven comparison and awareness content. Pulls organic search traffic. |
| Download | external link | Sends visitors to wp.org plugin page. |

Header navigation: **Home · Documentation · Blog · Download** (Download is the primary CTA button, purple).

---

## Documentation (`/unipixel-docs/`)

Organised primarily by platform, with feature/topic articles alongside.

### Setup guides — one per platform
- Setting Up UniPixel With: Meta (`/unipixel-docs/setting-up-unipixel-with-meta/`)
- Setting Up UniPixel With: Google (`/unipixel-docs/getting-ready-for-unipixel-what-you-need-from-google/` and related)
- Setting Up UniPixel With: TikTok (`/unipixel-docs/setting-up-unipixel-with-tiktok/`)
- Setting Up UniPixel With: Pinterest (`/unipixel-docs/setting-up-unipixel-with-pinterest/`)
- Setting Up UniPixel With: Microsoft (`/unipixel-docs/setting-up-unipixel-with-microsoft/`)

These are the most-visited docs — visitors arrive from "where do I find my Meta access token" type searches. Each is ~2,000 words, walks through credential gathering on the platform's UI step-by-step, then where to paste them in UniPixel.

### Feature / topic articles
- What Does UniPixel Actually Do?
- Custom Event Tracking with UniPixel (`/unipixel-docs/custom-event-tracking/`)
- Advanced Matching Setting with UniPixel (`/unipixel-docs/advanced-matching-setting-with-unipixel/`)
- Using UniPixel with Google Tag Manager (GTM) (`/unipixel-docs/using-unipixel-with-google-tag-manager-gtm/`)
- Stored Event Logs — See Exactly What Your Tracking Sends
- Cookie Consent & Tracking: UniPixel Keeps You Compliant

The plugin's admin help-icon popovers and Need-help-? links point at these URLs. Keeping URLs stable matters — see CLAUDE.md § Stable contract for the consent string keys; same principle applies here.

### Gaps worth filling
- No setup guide for "first time installing UniPixel" — a one-page index of "go through this in order"
- Cookie Consent doc was written before the v2.6.4 / v2.6.5 multi-language + customisable popup work — needs a refresh
- **Custom Event Tracking doc** (`/unipixel-docs/custom-event-tracking/`) predates v2.6.6 and now understates capability significantly. Needs rewrite covering URL trigger, page picker, "Any URL" mode, standard event name dropdowns, and the new Centralised Event Manager (cross-platform conversion setup, conceptual events like Lead / Newsletter Signup, group lifecycle).
- **New doc: Centralised Event Manager guide** — could be a dedicated article alongside the refreshed Custom Events one. Walks through "set up a Lead conversion across all platforms in one go", screenshots of the builder, what propagates vs what stays per-platform.
- **New doc: Tracking lead-gen / non-WooCommerce sites with UniPixel** — opens the new audience (B2B, services, membership, courses). Shows thank-you-page tracking, newsletter signup tracking, contact form tracking — all without GTM and without CSS for URL-based events.
- No troubleshooting guide

---

## Blog (`/blog/`)

5 articles as of the last check (April 2026). Two patterns dominate: **competitor comparisons** and **universal awareness pieces**.

### Comparison / "alternatives" articles
These target buying-intent searches like "X alternative" / "vs X":
- Conversios Alternatives? Check Hidden Costs First (Apr 7 2026)
- PixelYourSite? Watch Out for These Problems (Apr 7 2026)
- Meta Pixel Alternatives for WordPress in 2026 (Mar 25 2026)
- The Best Stape Alternatives for WordPress in 2026 (Mar 25 2026)

Pattern: ~2,200-2,400 words. Sets up what the competitor does → identifies its limitations or hidden costs → introduces UniPixel as the alternative → comparison table → "why UniPixel wins" numbered list → closing CTA. The Stape one sources off `marketing-knowledge/stape-alternatives.md`.

### Awareness articles
Universal-pillar content; don't lead with UniPixel:
- Your Ad Platforms Are Making Decisions With Missing Data (Apr 6 2026) — fits Pillar 1 ("ads wasting money you can't see")

### Gaps / next articles to write
Per `priorities.md` and `campaigns.md`, content for both Universal and Competitive pillars is on the backlog. Likely candidates:
- "Free Meta CAPI plugin for WordPress" (Competitive, intercepts buying-intent)
- "Why your WordPress site doesn't need a GTM server container" (Universal, education)
- "Server-side tracking on WordPress: the budget version" (Competitive, price-conscious search)
- **v2.6.6 announcement: "Set up a Lead conversion once. UniPixel handles all five platforms."** (announcement + feature deep-dive). Captures the moment of the Centralised Event Manager release. The lede angle: every other tracking plugin makes you set the same conversion up five times in five different places with five different standard event names; UniPixel does it in one form.
- **"Track form submissions and thank-you pages on WordPress without code"** (Universal + lead-gen audience). Targets the new C5 audience — lead-gen / B2B / service / course-creator WordPress sites. Hook: "If you've ever set up a Meta `Lead` event and then realised TikTok calls it `Contact`, this is for you."
- **"WordPress lead-gen tracking without GTM, without CSS, without paying $359"** (Competitive, intercepts the "PYS for non-WC sites" search). Specifically counters the gap competitors leave: most tracking plugins lead with WooCommerce; non-WC sites are second-class.

---

## Linking strategy

Internal linking should reinforce all three SEO goals:

1. **Plugin admin → docs**: every help icon in the UniPixel admin links to a relevant `/unipixel-docs/...` URL. These URLs are part of the plugin source (`functions/unipixel-functions.php`) so a docs URL change means a plugin file change. **Treat docs URLs as a stable contract.**
2. **Blog → docs**: comparison articles should link "see the setup guide" deeper into `/unipixel-docs/...`. Docs articles should link laterally to other relevant docs.
3. **Blog → blog**: comparison articles should reference each other where relevant ("if you're comparing all the WordPress options, see also our Conversios piece").
4. **External → home/blog/docs**: Reddit / forum / Medium syndication should link back to whichever surface fits — comparison topics → blog, setup help → docs, brand intro → home.

---

## Content distribution

Channels content can syndicate to (with `<link rel="canonical">` pointing back to the unipixelhq.com original):

- **Medium** — comparison articles fit the audience; canonical links keep SEO credit on unipixelhq.com
- **Dev.to** — technical articles (custom events, server-side tracking deep-dives) fit the audience
- **WP Tavern** — pitch-able for newsworthy releases; not paid syndication
- **Reddit** — manual sharing in relevant subreddits (r/woocommerce, r/PPC) per Campaign 1; never auto-spammed

The unipixelhq.com publication is always the **canonical source**. Syndication is distribution, not primary publication.

---

## Maintenance cadence

- New blog article: aim for 1-2 per month, alternating Universal and Competitive pillars
- Docs articles: write whenever a new feature ships or a recurring support question emerges
- Cookie Consent doc: needs urgent refresh post v2.6.4 + v2.6.5 (multi-language + customisation features)
- Setup guides: review when platforms change their admin UI (rare but happens — e.g. Meta Business Suite renames)
