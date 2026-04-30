# Priorities — Where the Product Is Now

Medium-cadence file. What's done, what's blocking adoption, what that means for what we work on next. Update when circumstances shift (new blocker surfaces, a tier-1 item ships, install count jumps a tier). Prune — don't append month-over-month.

> **Core framing:** The core problem is solved. The plugin genuinely does what it says — server-side tracking across 5 platforms, self-hosted, with deduplication, consent, and Advanced Matching. The remaining blockers are not capability gaps — they're friction and distribution.

---

## What's done

- Server-side + client-side tracking with automatic deduplication across all 5 platforms (Meta, Google, TikTok, Pinterest, Microsoft)
- WooCommerce events fire automatically (Purchase, AddToCart, InitiateCheckout, ViewContent)
- Custom click/interaction event tracking from the WordPress admin
- Advanced Matching (hashed PII) across Meta, TikTok, Pinterest
- Microsoft CAPI full implementation (live on wp.org as of v2.6.0)
- AddToCart via WooCommerce AJAX fragment mechanism (live on wp.org as of v2.6.0)
- Optional server-side (users can start with just a Pixel ID, add server-side later)
- **Consent layer fully matured (v2.6.4 + v2.6.5):**
  - Built-in popup ships with translations for 18 languages, auto-matched to visitor locale
  - Every string editable from admin per language (brand voice / jurisdictional wording)
  - 5 popup layout styles (centred card, top bar, bottom bar, bottom-left corner, bottom-right corner)
  - Optional non-blocking mode (popup visible without dimmed backdrop, tracking still gated by consent)
  - Optional Reject all button (force-choice friendly, GDPR-clean)
  - Mobile-responsive — buttons stack on phones and corner layouts
  - Reads 9 third-party CMPs (OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, Moove GDPR, CookieAdmin / Softaculous)

---

## What's blocking adoption

### 1. Distribution — nobody knows it exists

~100 installs. Invisible on WordPress.org. No SEO presence. No community footprint. No brand. The product can compete with $359/yr PixelYourSite — but nobody is making the comparison because they haven't found UniPixel. **This is the primary blocker.** See `campaigns.md` § Growth Channels.

### 2. Onboarding — platform credential gathering is painful

The plugin setup itself is simple. But before a user can configure UniPixel, they need to navigate each platform's own interface — Meta Business Suite, Pinterest Ads Manager, TikTok Business Center, Google Analytics — to find pixel IDs, generate access tokens, locate API secrets. Each platform has different terminology, different UI, different steps. Some platforms actively recommend competitors during this process (Pinterest recommends PixelYourSite in their ads manager setup docs).

UniPixel can't control those platform interfaces but can mitigate with clear documentation on buildio.dev walking users through each platform step by step. Several of these docs already exist.

### 3. Custom events UI — CSS selectors are a developer concept

Custom event tracking currently requires users to type CSS selectors (`#id`, `.class`) to identify which elements to track. This works for developers but is unintuitive for store owners. A user who wants to track "this button" shouldn't need to know what a CSS selector is. A visual element picker or guided wizard would open custom events to non-technical users. **This is the one real product gap that limits who can use the plugin.**

Stopgap: docs article explaining custom events setup exists, ready to publish.

---

## What this means for priorities

The product is past the "does it work" stage. It's now "can people get to the point where it works for them." A fundamentally different problem — and the right problem to have.

- **Distribution comes first** — people can't hit onboarding friction if they never find the plugin. Growth work (forums, Google Ads, YouTube, docs, partnerships) dominates near-term attention. See `campaigns.md`.
- **Onboarding docs are partially in place** — keep publishing platform-specific setup guides on buildio.dev / unipixelhq.com.
- **Custom events wizard is the highest-impact remaining product work** — once distribution moves, this is the biggest unlock for the addressable user base.

---

## Feature priority framework

> The decision process for evaluating what to build next. Apply every time a feature is proposed.

### The principle: effort-to-impact ratio wins

A feature that takes 2 hours and prevents a user from bouncing on the settings page is worth more than a feature that takes 2 weeks and impresses users who are already committed. Prioritise by the ratio, not by impact alone.

### Evaluation criteria (in order)

1. **Does it prevent abandonment?** If a user installs UniPixel, opens the settings, and doesn't see something they expect (their CMP, their platform, a basic capability), they deactivate. Features closing these gaps come first regardless of how "small" they seem.
2. **What's the effort?** A feature that follows an existing pattern (e.g. adding a CMP parser when 6 already exist) is near-zero risk and near-zero design cost. Prioritise pattern-following work over novel architecture.
3. **Does it protect the install base or grow it?** Retention features are as valuable as acquisition features. A lost install is harder to win back than a new one is to gain.
4. **Is it a quick win?** If effort is under a day and it removes real friction, do it. Don't defer small things behind larger "strategic" features.

### Anti-patterns to avoid

- **"The system is solid so this can wait"** — Solid for existing users ≠ solid for the next user who has a different CMP / workflow / expectation.
- **"Low audience likelihood"** — Valid for deprioritising only when the effort is also high. If effort is trivial, low audience likelihood doesn't justify skipping.
- **"We should do the big thing first"** — Only if the big thing is blocking the small things. Otherwise ship the small things while planning the big ones.

### Note: v2.5.1 optional server-side as onboarding improvement

Making server-side tracking optional (v2.5.1) removes the credential wall from initial setup. New users can install, paste a Pixel ID, start tracking immediately — no access token required. Not a marketing angle — just an internal note that this feature quietly helps retention.

---

## Prioritised feature tiers

> Living list. Ordered by effort-to-impact ratio. For the full backlog with bucket breakdown, see `projects/release-log.md`.

### Tier 1: Quick wins — high impact, low effort

| Feature | Effort | Why | Status |
|---|---|---|---|
| Add Complianz CMP parser | ~1 hour | Popular WP-native CMP. Abandonment risk without. Parser pattern exists × 6. | Done |
| Add CookieYes CMP parser | ~1 hour | Large free tier, widely used. | Done |
| Add Moove GDPR CMP parser | ~1 hour | Common in WooCommerce specifically. | Done |
| Simplify consent dropdown | ~2 hours | 9-vendor dropdown → 2 clear options. | Done |

### Tier 2: Medium effort, high impact — needs assessment

| Feature | Effort | Why | Status |
|---|---|---|---|
| Setup wizard / onboarding flow | Days | Most deactivations in first 10 minutes. Guided first-run (connect, verify). Must come before custom events wizard. | Needs assessment |
| Custom events wizard | Days | Current CSS-selector UI unintuitive for non-developers. Visual element picker + templates + validation + test/preview. The highest-impact product work remaining. Stopgap docs article exists. | Needs assessment |
| Event diagnostics dashboard | Days | Users can't tell if the plugin is working. Health screen: last event, success/fail counts, connection status per platform. Data already in `unipixel_event_log`. | Not started |

### Tier 3: Known gaps, lower urgency

| Feature | Effort | Why | Status |
|---|---|---|---|
| Microsoft WooCommerce pipeline | Days | Only platform without server-first WooCommerce. Low audience for Bing Ads but visible gap. | **Done (v2.6.x work)** — code complete, Microsoft CAPI token access not self-service yet; server-side untested against live endpoint. Client-side UET confirmed. **Do not advertise CAPI until verified.** |
| Advanced Matching (billing address + AM fields) | Hours | Sends hashed PII to improve match quality. | Done (v2.5.3) |
| external_id population | Hours | **Assessed and deprioritised.** Platform upsell metric, not a genuine quality gap for guest-checkout WooCommerce. See `domain-knowledge/platform-discoveries.md` § META-002. |
| Additional platforms (Snapchat, LinkedIn) | Weeks | Pro tier candidates. Matters to agencies, not blocker for solo store owners. | Not started |

---

## Where we're headed — the 10,000-install milestone

Not a deadline, a target state. When we hit this, Pro tier at $89/yr goes live.

- 10,000 active installs on wordpress.org
- 20–30 genuine reviews, 4.5+ star average
- 3–5 blog posts ranking on page 1 for target search queries
- YouTube channel with 5–10 tutorial videos, 10k+ total views
- Established presence in 3–4 WooCommerce/WordPress communities
- Pro tier live at $89/yr with clear free vs Pro comparison
- 200–500 paying customers (2–5% conversion) = $18k–$44k/yr revenue

The gap between here (~100 installs) and there (10,000) is a distribution problem, not a product problem. Channels and priorities in `campaigns.md`.
