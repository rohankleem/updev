# Positioning

Foundational — what UniPixel is, how we talk about it, how we differentiate. Slow-changing. When something here shifts, campaigns and priorities follow.

---

## What UniPixel Is

UniPixel is a WordPress plugin that sends conversion and event tracking data directly from the WordPress server to ad platforms — Meta, Pinterest, TikTok, Google, and Microsoft. It does server-side tracking without requiring GTM server containers, third-party cloud services, or paid subscriptions.

The plugin handles WooCommerce ecommerce events automatically, provides a Centralised Event Manager that lets users set up custom conversions across every platform in one shot (no per-platform repetition, no CSS for URL-based conversions, automatic standard event names per platform), includes a fully-featured built-in consent layer (multi-language popup with editable wording, multiple layout styles, optional non-blocking mode, plus passthrough integration with nine major third-party CMPs), and handles event deduplication automatically across all platforms.

UniPixel is designed to be sold. The model is full-feature access with a lock-in gate — every user gets everything, then a paywall activates once they're embedded. Target price: $89/yr. The current free phase is validation — establishing UniPixel as the preferred plugin before pricing activates.

---

## Positioning — The Core Message

**"Server-side tracking that actually works, with zero setup complexity."**

The wedge into the market is not more platforms or more features. It's being the answer to: *"Meta says set up Conversions API — how do I do that simply?"*

---

## The 5 Sales Pillars — Why UniPixel

These are the customer-facing reasons someone should use UniPixel. Not technical features — actual outcomes a store owner cares about. Every piece of content (ads, Facebook posts, landing pages, readme, forum replies) should map to one or more of these pillars. The through-line across all five: **you're already spending money on ads — UniPixel makes sure that money works as hard as it can.**

### Why tracking matters at all

You're spending money on ads. Are they spending it well? Your ad platforms make automated decisions about who sees your ads, how much to bid, and where to allocate your budget — all based on the conversion data they receive. If that data is incomplete, every decision is wrong. You pay more to reach worse audiences, you kill campaigns that were actually working, and you scale campaigns that aren't. You're losing money you can't see — because the data that would show you is never arriving.

### Pillar 1: Your ads are wasting money you can't see

Ad blockers, iOS privacy, and browser restrictions silently hide your real sales from your ad platforms. Their algorithms optimise on incomplete data — so they find worse audiences and spend your budget on people who won't buy. UniPixel gets your real results through — directly from your server, bypassing everything that blocks browser-based tracking.

**The outcome:** Your ad platforms see more of your real conversions. Their algorithms make better decisions. Your cost per customer comes down. Your ad spend works harder.

### Pillar 2: No extra infrastructure. No extra bills.

Other solutions force you into $100–150/mo cloud servers and complex GTM container setups just to send conversion data. UniPixel works from your existing WordPress hosting — because your server can already do this. Nothing extra to build, manage, or pay for.

**The outcome:** Server-side tracking without the server. No cloud hosting to provision, no GTM expertise to learn, no infrastructure bills that grow with your traffic. It runs on what you already have.

### Pillar 3: Track lead-gen, signups and any custom conversion — across every platform in one shot

Not just WooCommerce. Lead forms, newsletter signups, contact submissions, thank-you pages, post-checkout pages — set up the conversions that actually drive your business **once**, in the Centralised Event Manager, and UniPixel fires them across every ad platform you use. Pick a page from your site instead of writing CSS. Pick "Lead" once instead of typing the right standard event name into five different platform settings pages.

**The outcome:** Non-WooCommerce sites get first-class tracking too. Your ad platforms learn which actions lead to customers. They find more people who take those actions. Your campaigns get smarter without you touching them. And you never set up the same conversion five times again.

### Pillar 4: Privacy and consent without the headache

GDPR, cookie laws, consent management — UniPixel handles it. Use the built-in consent popup (multi-language out of the box, editable wording, multiple layout styles, optional non-blocking mode) or pair UniPixel with one of the nine third-party CMPs it reads from. Either way, data only sends when visitors consent. No legal grey areas, no compliance anxiety.

**The outcome:** You stay compliant without becoming a privacy lawyer. Consent is checked before any data fires. The popup looks right on every device and speaks the right language for your visitors. Your visitors' choices are respected automatically.

### Pillar 5: Five ad platforms. One plugin. Minutes to set up.

Meta, Google, TikTok, Pinterest, Microsoft — all connected, all tracking, all deduplicating automatically. No developer needed. No documentation rabbit holes. No 40-step setup guides.

**The outcome:** You're live on five platforms from one install. Each platform gets accurate, deduplicated data. You set it up once and it runs.

### How the pillars map to content

| Pillar | Best for | Ad technique |
|---|---|---|
| 1 — Wasting money | Cold audiences, awareness, education | Sweep and Strike |
| 2 — No infrastructure | Competitor audiences (Stape, Conversios) | Perch and Poach |
| 3 — Custom events | Mid-funnel, existing WP users | Pinpoint and Panorama |
| 4 — Consent handled | GDPR-concerned audiences, EU market | Sweep and Strike |
| 5 — Easy setup | Competitor pain (PYS complexity), evaluation stage | Perch and Poach |

---

## The Industry Problem

> This is the peg. Everything hangs off this.

**1. Browser delivery is failing.** Advertising platforms need better data. The browser — the only way most sites have ever sent it — is increasingly unable to deliver it. The delivery mechanism is failing, resulting in inaccurate conversion reporting, inaccurate measurement, and reduced algorithm effectiveness. Platforms have invested significantly in server-side protocols (Meta Conversion API, Google Measurement Protocol, TikTok Events API) specifically to solve this. If you're not using them, you're on the back foot.

**2. Tracking is event-driven now — not just page views.** Platforms are hugely event-driven — clicks, form submissions, interactions, purchases, add-to-carts. GTM exists to manage this but requires GTM expertise. Most site owners can't manage that.

**3. Custom events need to be simple, AND they need to span platforms.** Customers need a simple way to track their own conversions — this thank-you page, this newsletter signup, this lead form. They need an interface that doesn't require GTM, doesn't require CSS expertise, and doesn't make them paste the same event into five different platform settings pages with five different standard event names.

**4. Consent is non-negotiable.** Don't send data when customers haven't consented. The industry is cracking down. Real compliance risk.

### The WordPress advantage — why containers don't apply

> One of UniPixel's most powerful selling points. Use it everywhere — ads, content, landing pages, forum replies.

Most WordPress users don't realise this: **WordPress already has a server running PHP. It can make HTTP calls to platform APIs directly. There is no need for an external server container.**

The entire server-side tracking industry is built around a problem that WordPress doesn't have. Shopify, Squarespace, Wix, and custom builds can't run arbitrary server-side code — so they **need** an external server (GTM server container, Stape hosting, cloud infrastructure) to make those API calls. That's a real requirement for those platforms.

But WordPress? Plugins run PHP on the server. UniPixel just uses that. It fires conversion data from the same server that serves the website — directly to Meta, Google, TikTok, Pinterest, Microsoft. No middleman, no extra infrastructure, no container.

**The industry narrative is wrong for WordPress users:**
- "You need a GTM server container" — no, you need a WordPress plugin
- "Server-side tracking requires cloud hosting" — no, your hosting already runs PHP
- "Budget $100–150/mo for infrastructure" — no, your server already does this
- "You need GTM expertise" — no, the plugin handles it

**Every competitor is solving the wrong problem for WordPress:**
- Stape hosts containers WordPress doesn't need
- Conversios requires GTM server containers WordPress doesn't need
- The whole GTM server-side ecosystem assumes you can't run code on your server — but WordPress can

### How UniPixel addresses all four

1. **Server-side delivery.** Sends data through the protocols platforms have built — directly from the WordPress server.
2. **Event-driven tracking.** Handles WooCommerce events automatically and doesn't require GTM.
3. **Custom events.** Centralised Event Manager inside WordPress to set up custom conversions across every platform in one go. Pick a page from your site for thank-you-page or lead-page tracking (no CSS), or define a click target. Then pick a conversion type (Lead, Newsletter Signup, Contact, Registration, or your own) and UniPixel fills in each platform's correct standard event name automatically (Meta "Lead", Google "generate_lead", TikTok "Contact"). Edit a shared field once and it propagates to every linked platform. No GTM, no code, no per-platform repetition.
4. **Consent.** Checks consent before any event fires. Provides its own built-in consent popup (18 languages out of the box, editable text per language, choice of layouts, optional non-blocking mode, mobile-responsive), or reads choices from any of 9 third-party CMPs if the visitor already uses one. Doesn't send data without permission.

### The solution

Send data through the server-side protocols platforms have built — more comprehensively, with better coverage, and more responsibly. The result: more accurate conversion reporting, better measurement, stronger algorithm performance, more effective ad spend.

UniPixel makes this accessible by handling the technical complexity inside WordPress — simple setup, and your server starts delivering data through those protocols directly to the platforms.

---

## Key Language Rules

> Non-negotiable. Apply to all content — copy, ads, readme, blog posts, videos, everything.

### Never absolute. Always comparative.

Server-side tracking does not give 100% of conversions. It gives more than browser-only tracking. The claim is always comparative.

- "More conversions reported" not "all conversions reported."
- "More accurate data" not "complete data."
- "Better optimisation" not "perfect optimisation."
- "Closer to reality" not "matches reality."

### It's the better move — not the fix.

Don't say server-side tracking fixes the problem. Say it's the better move. It's better than not doing it. That's the claim.

### Correlation will never be solved.

The gap between WooCommerce numbers and platform numbers will never be fully closed. Don't promise correlation. Don't imply it. Don't use "your numbers will match" as a selling point.

### Don't introduce cost as a value proposition until told.

Price is not the strategy right now. Do not lazily drop pricing, savings, "it's free", or competitor cost comparisons into any content as a flippant benefit. A pricing strategy may or may not be designed later — when it is, it will be deliberate. Until then, customers converge on market need and value delivery. Cost benefits are discovered after adoption, not used to drive it. This applies to all content: readme, ads, blog posts, videos, everything.

### Don't do Meta's marketing for them.

Platform warnings (Meta Events Manager match quality scores etc.) are self-serving — they want more data fed to them. A low score is still better than no score. Don't use platform warnings as pain points. That's doing Meta's marketing, not ours.

### UniPixel is designed to be sold.

Pro tier at $89/yr is coming. "Free" is not the differentiator. The competitive position must hold at any price point. Growth tactics (free tier) are not market positioning.

### No instructions in marketing copy.

"Install, paste your credentials" is too much detail. Marketing communicates the outcome and the simplicity — not the steps.

---

## Two Communication Pillars

All messaging, content, and advertising falls into one of two pillars. These serve different audiences at different stages of awareness and must not be blurred together.

### Pillar 1: Universal — "You need this and here's why"

Addresses WordPress / WooCommerce users who haven't yet switched on to server-side tracking. They may not know what CAPI is, may not realise their data delivery is degrading, or may think their current setup is fine. This pillar educates and creates demand.

**Universal is one industry problem with multiple consequences and pain points to press on.**

### Pillar 2: Competitive — "You already have a solution — here's why UniPixel is better"

Addresses people who already have tracking in place or have already tried solutions. They're using a competitor and unhappy with it, they've hit complexity walls, or they've been burned. This pillar is market differentiation — not education. These people know what server-side tracking is. They need a reason to switch or choose UniPixel.

**Competitive is three market differentiators — not five.** Earlier versions had five that leaned on "free" as a selling point. That stops working when Pro launches. Dropped.

---

## Ad Techniques — Three Directions

All UniPixel ads and content use one of three directional techniques. Choosing the right one depends on where the audience is — do they already have a specific problem, or do they not yet know something is wrong, or are they using a competitor?

| Technique | Direction | How it works | When to use | Established roots |
|---|---|---|---|---|
| **Pinpoint and Panorama** *(alias: Spark and Bang)* | Narrow → Wide | Lead with a hyper-specific micro-frustration sourced from real forums. The specificity creates trust ("how did they know?"), then the lens opens to reveal UniPixel solves the whole category. | They already have a specific problem. They're in the forum right now. They just saw the error. | Collier's "enter the conversation" + Kennedy's "Find Yourself Here" + specificity-credibility principle (Halbert, Schwartz) |
| **Sweep and Strike** | Wide → Narrow | Lead with a broad, universal fear that hits everyone ("your ad data is wrong"). Agitate the consequences. Then strike with UniPixel as the one sharp answer. | They don't know they have a problem yet. Education-first. Awareness-stage. | Classic PAS (Problem-Agitation-Solution) |
| **Perch and Poach** *(metaphor: busking outside the concert)* | Beside → Away | Place ads and presence exactly where competitor users already congregate — search terms, support forums, community spaces. Intercept users experiencing subconscious pain with the competitor. | They're already using a competitor. They're not looking for an alternative — but they're experiencing friction they've normalised. | Competitive proximity / conquest marketing, targeting subconscious not conscious dissatisfaction. |

**How they pair:** Sweep and Strike creates demand (Universal). Pinpoint and Panorama converts people already experiencing the problem (both pillars). Perch and Poach intercepts competitor users (Competitive). A campaign can run all three simultaneously.

---

## Competitive Landscape

### Direct competitors

| Plugin | Installs | Rating | Price/yr | Server-Side | Notes |
|---|---|---|---|---|---|
| **PixelYourSite Pro** | ~500k | 4.3/5 | $359 | Yes | Market leader. Pinterest/Bing paid add-ons. UX criticised as cluttered. |
| **Pixel Manager** (SweetCode) | ~50k | 4.9/5 | $149–228 | Direct CAPI from your server | Highest rated, on wp.org since 2013. WooCommerce-only by design. Strong Pro features (ACR nightly recovery, Google Enhanced Conversions, payment-gateway accuracy reporting). Microsoft/Pinterest/TikTok/server-side gated to Pro. |
| **Conversios** | ~60k | 4.3/5 | $250–499 | Needs GTM | Bundles product feed management. Server-side requires GTM server container. |
| **Meta for WooCommerce** | ~500k | 2.2/5 | Free | Meta CAPI only | Official Meta plugin. 308 one-star reviews. Breaks frequently. Meta-only. |
| **Google Site Kit** | ~5M | 4.2/5 | Free | No | Dashboard/reporting tool, not a conversion tracker. |

### Other notable players

| Plugin | Model | Notes |
|---|---|---|
| **wetracked.io** | SaaS, $49–249/mo | WooCommerce + Shopify. Cloud-based, not a WP plugin. |
| **Stape** | Plugin + hosting, ~$20/mo | Plugin free, hosting is the product. UniPixel eliminates the container entirely. |
| **Conversion Bridge** | Integration-focused | 55 WP plugins connected to 16 analytics + 6 ad platforms. More "Zapier for tracking." |
| **TrackSharp** | WP-native server-side | GA4 only. No Meta, TikTok, Microsoft. Niche. |

### UniPixel vs competitors — feature matrix

| Feature | UniPixel | PixelYourSite Pro | Pixel Manager Pro | Conversios Pro | Meta for Woo |
|---|---|---|---|---|---|
| Meta Pixel + CAPI | Yes | Yes (free!) | Pro only | Pro only | Yes |
| GA4 + Measurement Protocol | Yes | Pro | Pro | Pro | No |
| TikTok + Events API | Yes | Pro | Pro | Pro | No |
| Microsoft UET | Yes | Add-on ($) | Pro | Pro | No |
| Pinterest | Yes | Add-on ($) | Pro | Pro | No |
| Snapchat | No | No | Pro | Pro | No |
| LinkedIn | No | No | Pro | Pro | No |
| Server-side (no GTM) | Yes | Yes | Yes (via Cloud) | Needs GTM | Yes (Meta only) |
| Self-hosted (no vendor) | Yes | Yes | Yes | No (GTM hosting) | Yes (Meta only) |
| WooCommerce events | Yes | Yes | Yes | Yes | Partial |
| Custom click events | Yes | Pro | Pro | Pro | No |
| Non-WooCommerce sites | Yes | Yes | No | No | No |
| Consent management | Built-in popup (18 languages, editable, multi-layout) + reads 9 third-party CMPs | Separate plugin ($) | Built-in | Built-in | No |
| Event deduplication | All platforms | Yes | Yes | Yes | Meta only |
| Multi-pixel support | No | Pro | Pro | ? | No |
| GTM required | No | No | No | Yes (for SST) | No |
| Product feed sync | No | No | No | Yes | Meta only |
| Centralised cross-platform event setup (one-shot) | Yes | No | No | No | No |
| Standard event name auto-fill per platform | Yes | No | No | No | No |
| URL-based custom event triggers (no CSS) | Yes | No | No | No | No |
| Hashed PII / Enhanced Conversions / Advanced Matching | Yes — Meta, Google, TikTok, Pinterest, Microsoft | Pro (per-platform) | Pro (Google Enhanced Conversions, Meta) | Pro | Meta only |

---

## Why UniPixel beats each competitor

> The real competitive case, competitor by competitor. Not feature ticks — actual reasons someone would choose UniPixel. Must be honest and hold up under scrutiny.

### vs PixelYourSite Pro (~500k installs, $359/yr)

**Their strength:** Market leader. Largest install base. Meta in free version. Established trust.

**Where UniPixel wins:**
- All 5 platforms included — PYS charges extra for Pinterest and Microsoft
- Custom click events included — PYS paywalls these behind Pro
- Consent management built in — PYS sells ConsentMagic separately
- Simpler, cleaner interface — PYS UX widely criticised as cluttered
- Self-hosted server-side with no external infrastructure (same as PYS here, but without the add-on pricing model)
- Centralised cross-platform event setup — set up "Lead" once and UniPixel hits all 5 platforms with the right standard event name. PYS makes you configure each platform separately
- URL-based event triggers — pick a thank-you page from a dropdown without writing CSS. PYS's custom event UI is selector-only

**The pitch:** Everything PixelYourSite charges add-ons for, UniPixel includes. Same self-hosted approach, less complexity, no upsell treadmill, plus a Centralised Event Manager that no other plugin has.

### vs Pixel Manager / SweetCode (~50k installs, $149–228/yr)

**Their strength:** Highest-rated tracking plugin in the WP ecosystem (4.9/5, ~400 reviews). On wp.org since August 2013 — twelve years of WooCommerce-specific depth. Direct-from-server CAPI in Pro (no cloud routing). Real Pro-only features we don't match: **Automatic Conversion Recovery (ACR)** nightly retroactive replay of missed WC orders, Google Ads Enhanced Conversions, Conversion Adjustments, payment-gateway accuracy reporting, HPOS depth, 15+ CMP integrations.

**Where UniPixel wins:**
- **Non-WooCommerce sites are first-class** — Pixel Manager is WooCommerce-shaped end-to-end (events follow WC orders). Lead-gen / B2B / service / membership / course sites have no real path inside Pixel Manager. UniPixel's URL trigger + Centralised Event Manager + page picker target this directly.
- **All five platforms in the base product** — Pixel Manager's free tier is Google Ads + GA4 + Meta + Hotjar; Microsoft, Pinterest, TikTok, Snapchat, Reddit, LinkedIn are all Pro-gated. Server-side delivery is also Pro-only. UniPixel ships every platform with server-side included.
- **Centralised Event Manager** — Pixel Manager has no equivalent for setting up a Lead / Newsletter Signup / Contact conversion once across all platforms with the right standard event name per platform.
- **Built-in consent popup** — Pixel Manager integrates with 15+ third-party CMPs but ships no popup of its own. UniPixel ships its own popup (18 languages, editable per language, 5 layouts, optional non-blocking, mobile-responsive) AND reads from 9 CMPs if the visitor prefers one.
- **Hashed PII across every platform** — UniPixel sends Advanced Matching / Enhanced-Conversions-equivalent data to Meta, Google, TikTok, Pinterest, and Microsoft uniformly. Pixel Manager's equivalents are platform-specific Pro features.

**The pitch:** Pixel Manager is the strongest WC-shaped tracking plugin on wp.org, and if your site is a WooCommerce store with offsite payment gateways, ACR alone may justify it. UniPixel is the alternative when your site isn't shaped like a WC store, when you want every platform without a Pro upgrade, or when you want cross-platform conversions configured in one form.

**Don't claim:** that Pixel Manager routes data through a cloud (it doesn't — Pro CAPI is direct from the server). That framing was wrong in earlier versions of this doc and must not appear in articles or ads.

### vs Conversios (~60k installs, $250–499/yr)

**Their strength:** Bundles product feed management. Strong GA4 integration.

**Where UniPixel wins:**
- No GTM server container required — Conversios requires separate cloud hosting ($100–150/mo), 15–20 hours of setup, ongoing maintenance
- Works on non-WooCommerce sites
- Simpler setup — no GTM expertise needed
- No invisible infrastructure costs
- Centralised Event Manager — Conversios needs per-platform GTM tag setup for each conversion you want to track; UniPixel does it in one form

**The pitch:** Conversios makes you build and maintain server infrastructure just to send conversion data. UniPixel uses the server you already have, and lets you set up a Lead conversion once across all platforms instead of building five separate GTM tags.

### vs Meta for WooCommerce (~500k installs, free)

**Their strength:** Official Meta plugin. Free. First thing people find when Meta tells them to set up CAPI.

**Where UniPixel wins:**
- Actually works — Meta for WooCommerce rated 2.2/5 with 308 one-star reviews
- 5 platforms, not 1
- Custom events support
- Consent management
- Doesn't break sites

**The pitch:** Meta's plugin is the obvious first choice. It's also rated 2.2 stars for a reason. UniPixel covers Meta and four other platforms without breaking your site.

### vs Stape (plugin free, hosting ~$20/mo)

**Their strength:** Focused product. Good GTM server container hosting. Clear pricing tiers.

**Where UniPixel wins:**
- No server container needed — Stape's entire model is hosting GTM server containers
- No GTM expertise required
- No ongoing infrastructure to manage
- No infrastructure dependency — if Stape outages or you stop paying, tracking stops

**The pitch:** Stape sells hosting for infrastructure UniPixel doesn't need. Full article at `stape-alternatives.md`.

### vs wetracked.io ($49–249/mo)

**Their strength:** Purpose-built for ecommerce attribution. Shopify + WooCommerce. Advanced attribution modelling.

**Where UniPixel wins:**
- WordPress-native — wetracked.io is SaaS, data goes to their cloud
- No monthly SaaS bill
- Self-hosted
- Works on any WordPress site

**The pitch:** Different tools for different needs. If all you need is accurate server-side tracking, UniPixel does it without the SaaS bill.

---

## UniPixel's genuine differentiators

1. **Self-hosted server-side tracking — zero external dependencies.** Conversios needs a GTM server container; Stape and the GTM-server ecosystem need separate cloud hosting; SaaS players (wetracked.io) route through their own cloud. UniPixel and PYS Pro both run direct CAPI from the WP server; Pixel Manager Pro also runs direct CAPI but is WC-only. The honest framing: "no external infrastructure required, no container, no SaaS" — true against Conversios / Stape / wetracked, level with PYS / Pixel Manager Pro on this dimension specifically.
2. **All 5 platforms at one price, no add-on upsells.** Meta + Pinterest + TikTok + Google + Microsoft in the base product.
3. **Centralised cross-platform event setup.** Set up Lead, Newsletter Signup, Contact, Registration, or any custom conversion ONCE. UniPixel applies it to every platform you've enabled with the correct standard event name (Meta `Lead`, Google `generate_lead`, TikTok `Contact`, Pinterest `Lead`, Microsoft `lead`). Edit a shared field once and it propagates to every linked platform. No competitor in the WordPress space offers this — every other plugin makes you set the same conversion up five times.
4. **Lead-gen / non-WooCommerce sites are first-class.** URL-based triggers + Centralised Event Manager mean a thank-you-page conversion takes thirty seconds without touching CSS or GTM. The Lead, Newsletter Signup and Contact conceptual events target the lead-gen / B2B / service / membership market directly. Most tracking plugins lead with WooCommerce; UniPixel works the same on non-WC sites.
5. **Consent layer is a real feature, not a checkbox.** Own popup ships with translations for 18 languages out of the box, every string is editable from the admin per language, choose from 5 layout styles (centred card, top/bottom bar, corner cards), optional non-blocking mode, mobile-responsive. Reads from 9 third-party CMPs (OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, Moove GDPR, CookieAdmin/Softaculous) if visitors prefer to use one of those instead.
6. **Lightweight — no build step, no external JS bundles.** Pure PHP + vanilla JS.

---

## GTM positioning — clarity note

> Not a front-facing differentiator. Secondary benefit, belongs toward the back of any feature list (~8th out of 12). Writing it down to prevent it being overstated.

**What UniPixel actually does:** Allows users to manage custom event tracking (button clicks, form submissions, interactions) inside WordPress — removing the need to use GTM for that specific task.

**What UniPixel does NOT do:** Replace GTM for script inclusion, tag sequencing, or many other things GTM handles. UniPixel works alongside GTM — cooperates, does not compete.

**How to communicate it (when it comes up):**
- Headline: *"Manage custom events without needing Google Tag Manager"*
- Extended: *"UniPixel lets you set up and manage custom event tracking inside WordPress — so in some cases you can reduce or remove your dependency on GTM for event management."*
- Never say "No GTM" as a standalone claim — it's misleading.
- Never position GTM as the enemy. Many UniPixel users will also use GTM. They coexist.

---

## UniPixel's gaps (honest)

| Gap | Impact | Priority |
|---|---|---|
| No Snapchat, LinkedIn, X, Reddit | Matters to agencies. Not a blocker for solo store owners. | Medium — Pro tier candidates |
| One pixel ID per platform | Edge case. Mostly agencies. | Low |
| No first-party proxy for ad blocker resilience | Server-side survives; client-side doesn't. | Medium |
| No product feed management | Conversios bundles this. Not our lane. | Low |
| ~100 installs, 6 reviews | Affects search ranking, trust, conversion. Growing. | Critical — actively improving |

---

## Messaging voice

**Tone:** Direct. Technical credibility without jargon. Anti-hype.

**What we sound like:**
- "Your ad data is wrong. Here's why."
- "Server-side tracking from your own server. No cloud. No extra infrastructure."
- "Meta says set up CAPI. UniPixel handles it inside WordPress."

**What we don't sound like:**
- "Supercharge your marketing with AI-powered analytics!"
- "The #1 most powerful tracking solution"
- "Limited time offer!"
- "Trusted by 10,000+ businesses"

### Core copywriting principles

1. Lead with the problem, not the feature.
2. Name the cost of not acting — but don't oversell. It's the better move, not the fix.
3. Be specific — "Meta Conversion API, GA4 Measurement Protocol, TikTok Events API" not "all major platforms."
4. For Competitive content: differentiate on simplicity, self-hosting, reliability — not price.
5. For Universal content: educate without selling — let the reader arrive at "I need this" on their own.
6. Never absolute. Always comparative.
7. Never lie about install counts or social proof.
8. Don't promise correlation between WooCommerce and platform numbers.
9. Don't use platform warnings as pain points.
10. Don't include setup instructions in marketing copy.

---

## Pricing strategy — lock-in gate model

### The model

Every user gets the full product. No feature gates, no crippled free tier, no "upgrade to unlock platforms." All 5 platforms, server-side tracking, Advanced Matching, consent management, custom events — everything works from day one.

Once the user is embedded — platforms configured, credentials entered, data flowing, events deduplicating — a paywall activates. The plugin continues working, but requires a paid licence to stay active past the gate.

**Why this works:** The switching cost does the selling. A user who has configured Meta, TikTok, Pinterest, Google, and Microsoft with access tokens, pixel IDs, custom events, and consent rules is not going to tear it all out and start over with a competitor. The value is proven by the time the gate hits. The $89/yr ask is trivial against that.

**Why not feature-gated freemium:** Feature gates create a worse product experience during evaluation — exactly when you need to prove value.

### Trigger mechanism — options under consideration

| Trigger | How it works | Pros | Cons |
|---|---|---|---|
| Time-based | Full access for N days (e.g. 14 or 30), then paywall | Simple, standard trial model | Users can reinstall/reset; time pressure may rush evaluation |
| Event-count | Full access until N server-side events sent, then paywall | Directly tied to value delivered; scales with store size | Requires tracking infra; small stores may never hit; number feels arbitrary |
| Hybrid | Whichever comes first — time OR event count | Covers both edge cases | More complex to communicate |

Decisions pending: trigger type, threshold, licence validation approach, grandfathering policy for pre-paywall installs, wp.org listing language (must comply with plugin directory guidelines), onboarding messaging.

### Price point

**$89/yr.** The floor. Sits at the "impulse buy for a business" threshold. Competitors charge $359 (PYS Pro) and $499 (Conversios). Room to go higher ($99–129/yr) once retention data proves users stick.

### Pricing landscape

| Plugin | 1-site annual |
|---|---|
| Conversios Pro | $499 (often "50% off" = ~$250) |
| PixelYourSite Pro | $359 |
| Pixel Manager Pro | $149–228 |
| **Market sweet spot** | **$79–149** |
| UniPixel (current) | Free |

### Revenue modelling (at target scale)

| Active Installs | Conversion Rate | Paying Users | At $89/yr |
|---|---|---|---|
| 10,000 | 2% | 200 | $17,800/yr |
| 10,000 | 5% | 500 | $44,500/yr |
| 10,000 | 10% | 1,000 | $89,000/yr |
| 30,000 | 3% | 900 | $80,100/yr |

Industry freemium average: 1–3%. Lock-in gate model should outperform because users experience the full product before the gate. Target: 5–10%.

### Introductory offers — options (for deployment once gate is live)

- Early-bird annual: $59/yr first year, $89/yr after
- Lifetime deal: one-time $199–249
- First-year discount: 30–50% off for trial converters

---

## Market trends

1. **Server-side tracking is now table stakes**, not a premium feature. Drivers: Safari ITP, Chrome privacy sandbox, Firefox ETP, iOS ATT, ad blockers (~43% adoption). Differentiator is how simply you deliver it.
2. **Google Consent Mode v2** mandatory for EU traffic since March 2024. Server-side fills modelling gaps.
3. **The pain is complexity, not missing platforms.** Users want Meta + Google to work properly without a $350 plugin or a GTM consultant.
4. **"No GTM" is becoming a selling point.** GTM server-side setup is 15–20 hours and $100–150/mo. WordPress-native alternatives are in demand.

---

## Audiences

### Pillar 1: Universal audiences (create demand)

**U1: "I have a pixel and I think it's working fine"** — largest group by volume, least aware. Installed a pixel years ago, see some conversions, assume it works. *Where they are:* general WooCommerce groups, small business forums. Not searching for tracking solutions.

**U2: "Meta / Google keeps warning me about data quality"** — seen warnings in Events Manager / GA4. Know something's wrong but don't understand server-side. *Where they are:* searching "meta event match quality", "facebook pixel not tracking all conversions".

**U3: "I know ad blockers are a problem but I don't know the solution"** — understand privacy changes affect tracking but haven't connected to server-side. *Where they are:* ecommerce communities, PPC forums.

### Pillar 2: Competitive audiences (differentiate)

**C1: "Meta told me to set up CAPI — what plugin do I use?"** — accepted they need CAPI. Shopping. Comparing features and prices. *Where they are:* searching "best meta conversion api plugin woocommerce".

**C2: "I can't justify $350/yr just for tracking"** — found paid solutions, bounced on price. Small WooCommerce stores. *Where they are:* WP.org plugin search, "pixelyoursite free alternative".

**C3: "I tried GTM server-side and it's too complex"** — attempted and failed/gave up. *Where they are:* Conversios/Stape support threads complaining about setup.

**C4: "I'm using Meta for WooCommerce but it's terrible"** — installed the official plugin because free and official. Then it broke their site. *Where they are:* Meta for WooCommerce support forum, Facebook groups.

**C5: "I run a non-WooCommerce WordPress site and tracking plugins feel WC-shaped"** — lead-gen sites, B2B services, agencies, course creators, membership sites, consultancy portfolios. They have form submissions and thank-you pages, not orders. They want to fire Meta `Lead` on `/thank-you/` and have Google get `generate_lead` for the same action. Most tracking plugins lead with WooCommerce, paywall custom events behind Pro tiers, and force per-platform setup. *Where they are:* WP.org searches like "lead tracking wordpress without gtm", "thank you page conversion tracking wordpress", "form submission meta capi wordpress", lead-gen Facebook groups, B2B WordPress communities.

### How the two pillars work together

Universal creates awareness, Competitive differentiates.

Someone reads "Why your ad data is wrong" (Universal) → understands the problem → searches for a solution → finds "How to set up Meta CAPI for free on WooCommerce" (Competitive). Pipeline works because both exist.

The wp.org readme must serve both pillars — Universal visitors need to understand why they need this; Competitive visitors need features, platforms, and comparison info.
