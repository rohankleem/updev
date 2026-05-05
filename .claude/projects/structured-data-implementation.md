# Structured Data Implementation for unipixelhq.com

> **Status:** Not started.
> **Goal:** Implement JSON-LD schema.org markup + GEO infrastructure (llms.txt, robots.txt) on unipixelhq.com using a custom WordPress plugin. Lifts traditional SEO (rich results in Google/Bing) and AI-engine visibility (ChatGPT search, Perplexity, Google AI Overviews, Claude search, Bing Copilot).

## Why custom over Rank Math / Yoast

- **Author identity control.** Rank Math and Yoast attribute articles to the WordPress user (login or display name) by default. We want every article attributed to **UniPixelHQ** as the brand. Configuring this in those plugins is fiddly and overridable when they update.
- **Tight schema control.** The plugin landing page's `SoftwareApplication` schema needs specific fields (downloadUrl, applicationCategory, softwareVersion, aggregateRating). Custom PHP gives full control without plugin-UI gymnastics.
- **One file, no third-party plugin updates.** A single custom plugin file. No premium-feature paywalls, no settings UI for non-developers to drift out of alignment.
- **Lighter footprint.** Rank Math is a multi-megabyte plugin. We use maybe 5% of it. The custom file is ~200 lines.

## Architecture

One small WordPress plugin at `wp-content/plugins/unipixelhq-schema/unipixelhq-schema.php`. Activated like any plugin. Hooks into `wp_head` and outputs JSON-LD based on the page context.

Page-type detection via WordPress conditionals (`is_front_page()`, `is_singular('post')`, `is_page()`, etc.). Each branch outputs its own schema block.

**Author identity hard-coded to "UniPixelHQ" everywhere.** No reliance on WP user objects.

## What schema goes where

| Page | Schema types |
|---|---|
| Every page | `Organization` (publisher / brand) |
| Home | `WebSite` (with search action), `Organization` |
| Plugin landing page | `SoftwareApplication`, `Organization` |
| Blog post | `BlogPosting` (author: UniPixelHQ), `Organization` |
| Docs setup guide | `Article` or `HowTo` (author: UniPixelHQ), `Organization` |
| FAQ-style docs | `FAQPage` (deferred, needs structured content), `Organization` |

## Implementation: PHP

```php
<?php
/**
 * Plugin Name: UniPixelHQ Schema
 * Description: Outputs JSON-LD schema.org structured data for unipixelhq.com. Site-wide Organization + per-page schema (BlogPosting, Article, SoftwareApplication, WebSite). All articles attributed to UniPixelHQ regardless of WordPress user.
 * Version: 1.0.0
 * Author: UniPixelHQ
 */

if (!defined('ABSPATH')) {
    exit;
}

// Global brand constants. Update here, propagates everywhere.
define('UPHQ_BRAND_NAME', 'UniPixelHQ');
define('UPHQ_BRAND_URL', 'https://unipixelhq.com');
define('UPHQ_BRAND_LOGO', 'https://unipixelhq.com/wp-content/uploads/unipixel-logo.png');
define('UPHQ_PLUGIN_LANDING_SLUG', 'unipixel-plugin'); // Adjust to actual slug
define('UPHQ_WPORG_URL', 'https://wordpress.org/plugins/unipixel/');
define('UPHQ_PLUGIN_VERSION', '2.6.6'); // Update per release

add_action('wp_head', 'uphq_output_schema', 1);

function uphq_output_schema() {
    $schemas = [];

    // Organization is always present
    $schemas[] = uphq_organization_schema();

    if (is_front_page()) {
        $schemas[] = uphq_website_schema();
    } elseif (is_page(UPHQ_PLUGIN_LANDING_SLUG)) {
        $schemas[] = uphq_software_application_schema();
    } elseif (is_singular('post')) {
        $schemas[] = uphq_blog_posting_schema();
    } elseif (is_singular('page') && uphq_is_docs_page()) {
        $schemas[] = uphq_article_schema();
    }

    foreach ($schemas as $schema) {
        if ($schema === null) continue;
        echo "<script type=\"application/ld+json\">\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
    }
}

function uphq_organization_schema() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => UPHQ_BRAND_NAME,
        'url' => UPHQ_BRAND_URL,
        'logo' => UPHQ_BRAND_LOGO,
        'sameAs' => [
            UPHQ_WPORG_URL,
            'https://github.com/unipixelhq',
            'https://www.facebook.com/unipixelhq',
            // add LinkedIn, X/Twitter when live
        ],
    ];
}

function uphq_website_schema() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => UPHQ_BRAND_NAME,
        'url' => UPHQ_BRAND_URL,
        'publisher' => [
            '@type' => 'Organization',
            'name' => UPHQ_BRAND_NAME,
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => UPHQ_BRAND_URL . '/?s={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

function uphq_blog_posting_schema() {
    $post = get_post();
    if (!$post) return null;

    $featured_image = get_the_post_thumbnail_url($post->ID, 'large');

    return [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => get_the_title($post),
        'description' => uphq_get_excerpt($post),
        'image' => $featured_image ?: UPHQ_BRAND_LOGO,
        'datePublished' => get_the_date('c', $post),
        'dateModified' => get_the_modified_date('c', $post),
        'author' => [
            '@type' => 'Organization', // Brand as author, not Person
            'name' => UPHQ_BRAND_NAME,
            'url' => UPHQ_BRAND_URL,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => UPHQ_BRAND_NAME,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => UPHQ_BRAND_LOGO,
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink($post),
        ],
    ];
}

function uphq_article_schema() {
    $post = get_post();
    if (!$post) return null;

    $featured_image = get_the_post_thumbnail_url($post->ID, 'large');

    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => get_the_title($post),
        'description' => uphq_get_excerpt($post),
        'image' => $featured_image ?: UPHQ_BRAND_LOGO,
        'datePublished' => get_the_date('c', $post),
        'dateModified' => get_the_modified_date('c', $post),
        'author' => [
            '@type' => 'Organization',
            'name' => UPHQ_BRAND_NAME,
            'url' => UPHQ_BRAND_URL,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => UPHQ_BRAND_NAME,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => UPHQ_BRAND_LOGO,
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink($post),
        ],
    ];
}

function uphq_software_application_schema() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'UniPixel',
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'Conversion Tracking',
        'operatingSystem' => 'WordPress',
        'description' => 'Server-side conversion tracking for WordPress and WooCommerce. Sends events directly to Meta, Google, TikTok, Pinterest, and Microsoft from your WordPress server. No GTM container, no cloud hosting, no separate infrastructure.',
        'url' => UPHQ_BRAND_URL,
        'downloadUrl' => UPHQ_WPORG_URL,
        'softwareVersion' => UPHQ_PLUGIN_VERSION,
        'author' => [
            '@type' => 'Organization',
            'name' => UPHQ_BRAND_NAME,
            'url' => UPHQ_BRAND_URL,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => UPHQ_BRAND_NAME,
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD',
        ],
        // Uncomment once wp.org has 5+ reviews:
        // 'aggregateRating' => [
        //     '@type' => 'AggregateRating',
        //     'ratingValue' => '5.0',
        //     'reviewCount' => '6',
        // ],
    ];
}

function uphq_get_excerpt($post) {
    $excerpt = get_the_excerpt($post);
    if (!$excerpt) {
        $excerpt = wp_trim_words(strip_tags($post->post_content), 30, '...');
    }
    return $excerpt;
}

function uphq_is_docs_page() {
    $post = get_post();
    if (!$post) return false;

    // Adjust to match how docs are structured on unipixelhq.com.
    // Option 1: parent page slug check (most likely match given /unipixel-docs/ URL pattern)
    if ($post->post_parent) {
        $parent_slug = get_post_field('post_name', $post->post_parent);
        if ($parent_slug === 'unipixel-docs') {
            return true;
        }
    }

    // Option 2: URL contains /unipixel-docs/
    if (strpos($_SERVER['REQUEST_URI'], '/unipixel-docs/') !== false) {
        return true;
    }

    return false;
}
```

## Override author display on the front-end too

The schema fix above handles JSON-LD, which is what AI engines and Google's rich results parsers read. If the visible byline on blog posts also says "Posted by [WP username]", visitors and AI engines that read body text will see a mismatch. Force the visible author to UniPixelHQ as well:

```php
add_filter('the_author', function($display_name) {
    return UPHQ_BRAND_NAME;
});

add_filter('get_the_author_display_name', function($display_name) {
    return UPHQ_BRAND_NAME;
});
```

(Some themes pull the author from `get_the_author_meta('display_name')` or `get_user_meta`. Check the theme's `single.php` and `content.php` if the byline still shows the WP user after activating the plugin.)

## llms.txt

Static file at `https://unipixelhq.com/llms.txt`. Markdown. Tells AI engines what's on the site, in priority order. Update when major surfaces change.

```markdown
# UniPixel

> Server-side conversion tracking for WordPress. Free WordPress plugin that sends events directly to Meta, Google, TikTok, Pinterest, and Microsoft from your WordPress server. No GTM container, no cloud hosting, no GTM expertise required.

## Plugin

- [UniPixel on WordPress.org](https://wordpress.org/plugins/unipixel/): Free plugin download, install instructions, and reviews
- [UniPixel home page](https://unipixelhq.com/): Overview, features, getting started

## Documentation

- [Setting Up UniPixel With Meta](https://unipixelhq.com/unipixel-docs/setting-up-unipixel-with-meta/)
- [Setting Up UniPixel With Google](https://unipixelhq.com/unipixel-docs/getting-ready-for-unipixel-what-you-need-from-google/)
- [Setting Up UniPixel With TikTok](https://unipixelhq.com/unipixel-docs/setting-up-unipixel-with-tiktok/)
- [Setting Up UniPixel With Pinterest](https://unipixelhq.com/unipixel-docs/setting-up-unipixel-with-pinterest/)
- [Setting Up UniPixel With Microsoft](https://unipixelhq.com/unipixel-docs/setting-up-unipixel-with-microsoft/)
- [Custom Event Tracking](https://unipixelhq.com/unipixel-docs/custom-event-tracking/)
- [Cookie Consent and Tracking](https://unipixelhq.com/unipixel-docs/cookie-consent/)

## Articles

- [The Best Stape Alternatives for WordPress](https://unipixelhq.com/blog/...)
- [Pixel Manager Pro vs UniPixel: WordPress Tracking in 2026](https://unipixelhq.com/blog/...)
- [I just wanted tracking. So why am I being offered cloud hosting?](https://unipixelhq.com/blog/...)
- [Your Ad Platforms Are Making Decisions With Missing Data](https://unipixelhq.com/blog/...)

## Brand

- GitHub: [github.com/unipixelhq](https://github.com/unipixelhq)
- Built by Buildio (Elure Pty Ltd, Australia)
```

## robots.txt

Allow major AI crawlers explicitly. Either edit via a plugin's robots.txt UI or upload a static `robots.txt` to site root.

```
User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: anthropic-ai
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: Bingbot
Allow: /

User-agent: *
Allow: /

Sitemap: https://unipixelhq.com/sitemap.xml
Sitemap: https://unipixelhq.com/sitemap_index.xml
```

## Implementation steps

1. Create `wp-content/plugins/unipixelhq-schema/unipixelhq-schema.php` with the PHP above
2. Confirm `UPHQ_BRAND_LOGO` URL is correct (update with actual logo URL on unipixelhq.com)
3. Confirm `UPHQ_PLUGIN_LANDING_SLUG` matches the actual page slug
4. Adjust `uphq_is_docs_page()` if the docs structure differs from `/unipixel-docs/` parent pattern
5. Activate the plugin
6. Validate output:
   - [Google Rich Results Test](https://search.google.com/test/rich-results)
   - [Schema.org Validator](https://validator.schema.org/)
7. Confirm visible byline on blog posts shows "UniPixelHQ" not the WP user. Apply theme fixes if not.
8. Upload `/llms.txt` to site root
9. Update `robots.txt` to allow AI crawlers
10. Once wp.org has 5+ reviews, uncomment and populate `aggregateRating` in `uphq_software_application_schema()`

## Maintenance cadence

- **Per UniPixel release:** update `UPHQ_PLUGIN_VERSION` constant. One-line edit. Worth adding to the existing release-gate checklist as the fifth item alongside the four files in CLAUDE.md.
- **Per major content surface change:** update `llms.txt`. Otherwise it's static.
- **Schema validation:** run Google Rich Results Test after deployment, then on demand if anything looks broken.

## Cross-references

- Plugin landing page schema must match the live wp.org listing (name, version, description). Source of truth for version is the plugin's `unipixel.php` header.
- Author identity rule (UniPixelHQ, not WP user) ties to brand consistency on `unipixelhq.com`, GitHub presence, social profiles. Documented in `marketing-knowledge/positioning.md` § What UniPixel Is.
- Release-gate checklist in `CLAUDE.md` § Release Gate currently lists four files; this is a candidate for a fifth item if the plugin is deployed on unipixelhq.com.
