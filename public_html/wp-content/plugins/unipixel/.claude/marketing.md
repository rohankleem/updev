# UniPixel — Marketing Intelligence & Strategy

**Updated March 2026 | ~100 active installs | 6 five-star reviews**

---

## What UniPixel Is

UniPixel is a WordPress plugin that sends conversion and event tracking data directly from the WordPress server to ad platforms — Meta, Pinterest, TikTok, Google, and Microsoft. It does server-side tracking without requiring GTM server containers, third-party cloud services, or paid subscriptions.

The plugin handles WooCommerce ecommerce events automatically, provides an interface for custom event tracking (buttons, forms, interactions), includes built-in consent management reading from six major CMPs or its own popup, and handles event deduplication automatically across all platforms.

UniPixel is designed to be sold. The model is full-feature access with a lock-in gate — every user gets everything, then a paywall activates once they're embedded. Target price: $89/yr. The current free phase is validation — establishing UniPixel as the preferred plugin before pricing activates.

---

## Where the Product Is Now

> **The core problem is solved. The plugin genuinely does what it says — server-side tracking across 5 platforms, self-hosted, with deduplication, consent, and Advanced Matching. The remaining blockers are not capability gaps — they're friction and distribution.**

### What's done
- Server-side + client-side tracking with automatic deduplication across all 5 platforms
- WooCommerce events fire automatically (Purchase, AddToCart, InitiateCheckout, ViewContent)
- Custom click/interaction event tracking from the WordPress admin
- Advanced Matching (hashed PII) across Meta, TikTok, Pinterest
- Consent management reading 9 CMPs + own popup
- Optional server-side (users can start with just a Pixel ID, add server-side later)

### What's blocking adoption

**1. Distribution — nobody knows it exists.**
~100 installs. Invisible on WordPress.org. No SEO presence. No community footprint. No brand. The product can compete with $359/yr PixelYourSite — but nobody is making the comparison because they haven't found UniPixel. This is the primary blocker. See Growth Strategy section.

**2. Onboarding — platform credential gathering is painful.**
The plugin setup itself is simple. But before a user can configure UniPixel, they need to navigate each platform's own interface — Meta Business Suite, Pinterest Ads Manager, TikTok Business Center, Google Analytics — to find pixel IDs, generate access tokens, locate API secrets. Each platform has different terminology, different UI, and different steps. Some platforms actively recommend competitors during this process (Pinterest recommends PixelYourSite in their ads manager setup docs). UniPixel can't control these platform interfaces, but can mitigate this with clear documentation on buildio.dev walking users through each platform step by step. Several of these docs already exist.

**3. Custom events UI — CSS selectors are a developer concept.**
Custom event tracking currently requires users to type CSS selectors (#id, .class) to identify which elements to track. This works for developers but is unintuitive for store owners. A user who wants to track "this button" shouldn't need to know what a CSS selector is. A visual element picker or guided wizard would open custom events to non-technical users. This is the one real product gap that limits who can use the plugin. Stopgap: docs article explaining custom events setup exists and is ready to publish.

### What this means for priorities
The product is past the "does it work" stage. It's now "can people get to the point where it works for them." That's a fundamentally different problem — and it's the right problem to have. Distribution comes first (people can't hit onboarding friction if they never find the plugin). Onboarding docs are partially in place. The custom events wizard is the highest-impact remaining product work.

---

## Active Campaign Plan — March 2026

> **Focus: PixelYourSite displacement.** All three campaigns target the same competitor ecosystem from different angles simultaneously. Duration: next few weeks. Review and iterate based on results.

### Campaign 1: Organic Forum Presence — Perch and Poach

**Technique:** Perch and Poach
**Channel:** WordPress.org support forums, Reddit (r/woocommerce, r/PPC, r/FacebookAds), WooCommerce Facebook groups
**Cost:** Free — time only

**What to do:**
- Find active threads where people are struggling with PixelYourSite (CAPI setup, update breakages, consent conflicts, setup confusion, "is there an alternative?")
- Reply helpfully — answer the actual question, explain the concept, provide genuine value
- Mention UniPixel naturally at the end: _"If you want something that handles this automatically, UniPixel does it out of the box"_
- Collect real user language for future Pinpoint hooks (even threads you don't reply to are research)

**Target thread types:**
- CAPI not working / events doubling
- Plugin update broke tracking
- Consent plugin blocking pixel fires
- "What plugin should I use for WooCommerce tracking?"
- "Is there a simpler alternative to PixelYourSite?"
- Setup confusion — can't find where to enter credentials

**Tone:** Peer who's been there, not salesperson. Help first, mention second. Never bash PYS.

---

### Campaign 2: Meta/Instagram Ads — Pinpoint and Panorama Graphics

**Technique:** Pinpoint and Panorama
**Channel:** Meta Ads (Facebook + Instagram feed/stories)
**Targeting:** People who have searched for, used, or engaged with PixelYourSite content
**Format:** 3 graphic creatives (Pain/Rainbow format — designed in Figma)

**Audience targeting options:**
- Custom audience: website visitors who searched PixelYourSite-related terms
- Interest targeting: WooCommerce, Facebook Pixel, conversion tracking, digital advertising
- Lookalike from WordPress.org plugin page visitors (if pixel is on buildio.dev)

**Creative approach:** Each graphic leads with a hyper-specific Pinpoint hook (real forum language) that makes the viewer think "that's exactly my problem." The graphic then opens to the Panorama — UniPixel as the solution to a whole world of tracking pain.

**3 graphics — one per pain angle:**

| # | Pinpoint hook (graphic lead) | Panorama (graphic payoff) | Pain angle |
|---|---|---|---|
| 1 | _Use existing Pain/Rainbow graphic 1_ | — | Missing data / "your ad data is wrong" |
| 2 | _Use existing Pain/Rainbow graphic 2_ | — | Weaker ad performance / "your ads cost more than they should" |
| 3 | _New graphic — Pinpoint and Panorama format_ | — | Specific micro-frustration (e.g. "Why does Meta say 12 purchases but WooCommerce says 47?") |

> **Note:** Graphics 1 and 2 already exist (Sweep and Strike format). Graphic 3 is the new Pinpoint and Panorama creative — to be designed in Figma. The 3 run together as an A/B/C test.

---

### Campaign 3: Google Ads — Competitor Intercept (LIVE)

**Technique:** Perch and Poach
**Channel:** Google Search Ads (Search only — Display Network and Search Partners OFF)
**Targeting:** People actively searching for competitors by name
**Landing page:** https://unipixelhq.com
**Status:** Live as of 14 March 2026. All 5 competitor ad groups live (PYS, Stape, Conversios, PixelCat, Pixelavo). Meta Pixel ad group live. **Zero impressions 14–24 March** — caused by incomplete advertiser verification (see Learnings). Verification completed 24 March 2026.

#### Account setup

- **Google Ads account:** rohankleem@gmail.com (267-541-9427)
- **Business name:** Buildio
- **Billing entity:** Elure Pty Ltd (ACN — to be renamed to Buildio later)
- **Advertiser name:** Buildio

#### Campaign settings

| Setting | Value |
|---|---|
| Campaign type | Search (not Smart, not Performance Max) |
| Networks | Search only — Display Network OFF, Search Partners OFF |
| Bidding | Maximise clicks, max CPC A$0.35 |
| Daily budget | A$0.90 (reduced 16 March 2026 from A$6.00) |
| Locations | All countries and territories |
| Languages | English |
| AI Max | OFF |
| Ad rotation | Optimise: prefer best performing |
| Start date | 14 March 2026 |
| End date | Not set (manual pause) |

#### Keyword strategy — applies to all ad groups

- **Exact match only** — `[brackets]` on everything. Close variants are always on and cannot be disabled; Google expands exact match to cover reworded queries, added words, and same-intent searches automatically.
- **One keyword per small brand** — for WordPress plugin competitors (Conversios, PixelCat, Pixelavo), the bare brand name `[brand]` is sufficient. Close variants catch "brand plugin", "brand alternative", "brand wordpress" etc. These brands are small enough that nobody searches modifier phrases at volume.
- **Modifiers for big brands** — for well-known brands (Stape, PixelYourSite), the bare brand keyword catches too much low-intent traffic (people looking for docs, login, support). Modifiers filter for switching/evaluation intent. At higher volume, separate keywords also earn individual Quality Scores and give useful reporting granularity.
- **No bare brand keyword for big brands** — `[stape]` alone would show ads to existing Stape users searching for documentation. Waste of budget. Only use intent-filtered modifiers.
- **PYS exception** — PixelYourSite currently has both bare and modifier keywords. Consider trimming bare keywords (`[pixelyoursite]`, `[pixel your site]`) if data shows low-intent clicks.

#### Estimated performance

- Weekly clicks: ~8 on PYS terms (exact match, narrow competitor audience). Stape may add significant volume.
- Avg CPC: ~A$0.32
- Weekly cost: ~A$2.59 on PYS. Watch Stape — higher volume could consume budget. If Stape eats the full A$6/day, consider raising budget or splitting Stape into its own campaign.
- This is a sniper campaign — low volume, high intent. Volume comes from Campaign 4 (universal keywords, not yet built).

#### Campaign-level sitelinks (shared across all ad groups)

| Sitelink text (max 25 chars) | Description 1 (max 35 chars) | Description 2 (max 35 chars) | URL |
|---|---|---|---|
| 5 Platforms One Plugin | Meta TikTok Google Pinterest Bing | All server-side tracking built in | unipixelhq.com |
| Server-Side Built In | No extra servers or hosting | Tracks from your WordPress server | unipixelhq.com |
| See How It Works | Install, configure, done | Setup takes 2 minutes | unipixelhq.com |
| WooCommerce Ready | Purchase events fire automatically | Deduplication built in | unipixelhq.com |

---

#### Ad group: PixelYourSite - All Intent

**Keywords (all exact match):**

> **Updated 16 March 2026:** Trimmed to bare brand keywords only. All modifiers removed — close variants cover "pixelyoursite alternative", "pixelyoursite not working", etc. automatically. PYS is a WordPress plugin so bare keyword doesn't attract irrelevant doc-seekers like Stape would.

```
[pixelyoursite]
[pixel your site]
```

**Headlines (max 30 chars each):**

```
PixelYourSite Alternative
Tired of PixelYourSite?
$359/yr for Tracking? Really?
5 Platforms — One Plugin
PixelYourSite Keeps Breaking?
15 Pages of Ignored Tickets
CAPI Without the Headache
UniPixel — Install and Go
Events Doubling? We Fix That
TikTok + Pinterest Included
No Server Containers Needed
Setup Takes 2 Minutes
Your Tracking Shouldn't Break
Dashboard Full of Upsells?
Stop Paying $359/yr
```

**Descriptions (max 90 chars each):**

```
PixelYourSite charges $359/yr for TikTok and Pinterest. UniPixel includes all 5 platforms.
No 40-step setup guides. No dashboard upsell nags. Just server-side tracking that works.
Meta, Google, TikTok, Pinterest & Bing — CAPI built in, deduplication automatic. Install and go.
Tired of updates freezing your site? UniPixel is lightweight, reliable server-side tracking.
```

**Sitelinks:** Campaign-level (shared across all ad groups). See campaign sitelinks below.

**Callouts (max 25 chars each):**

```
5 Ad Platforms Built In
Works Out of the Box
WooCommerce Ready
Consent Built In
No Dashboard Upsell Nags
Auto Deduplication
Server-Side Tracking
Meta TikTok Google & More
Auto Purchase Tracking
```

#### PYS future ad group splits (when volume justifies)

| Ad group | Keywords | Ad copy angle |
|---|---|---|
| PYS - Exit Intent | alternative, replacement, review | "Looking for something simpler?" |
| PYS - Pain | not working, problems, issues, breaking | "Your tracking shouldn't break" |
| PYS - Brand Intercept | pixelyoursite, pixel your site, plugin | "5 platforms, one plugin" |
| PYS - Pricing | expensive, pricing, pro, $359 | "Stop overpaying for tracking" |

> **Note:** One ad group for now. Split when there's enough click data to justify different messaging per intent. The $6/day budget covers all ad groups within the campaign — splitting doesn't divide the budget.

---

#### Ad group: Stape

**Angle:** WordPress users don't need Stape's infrastructure. Your server already does this.

**Why Stape is different to other competitors:** Stape is a well-known cross-platform brand (not just WordPress). Bare `[stape]` would catch existing users looking for docs/login/support — wasted clicks. Modifiers filter for evaluation/switching intent. WordPress-specific ad copy acts as a secondary filter — non-WordPress users see "WordPress" and don't click, so you don't pay.

**Keywords (all exact match):**

```
[stape alternative]
[stape alternatives]
[stape replacement]
[stape review]
[stape reviews]
[stape too expensive]
[stape expensive]
[stape pricing]
[stape cost]
[stape not working]
[stape problems]
[stape issues]
[stape complicated]
[stape support]
[stape wordpress]
[stape woocommerce]
[stape wordpress alternative]
[stape server side tracking]
[stape gtm server container]
[stape gtm hosting]
[stape server container]
[stape conversions api]
[stape capi]
```

**Headlines (max 30 chars each):**

```
WordPress? Skip Stape
Your Server Already Does This
No Containers Needed
No GTM. No Stape. Done.
Server-Side Without Stape
Stop Paying for Infrastructure
5 Platforms — One Plugin
Built Into WordPress
CAPI Without Containers
WooCommerce Tracking — Sorted
No Cloud Hosting Needed
Stape Alternative for WordPress
Track From Your Own Server
Why Pay for a Container?
Setup Takes 2 Minutes
```

**Descriptions (max 90 chars each):**

```
UniPixel sends conversions from your WordPress server. No containers, no cloud, no Stape.
Meta, TikTok, Google, Pinterest & Bing — server-side tracking from your own server.
Stape sells hosting your server doesn't need. UniPixel plugs straight into WordPress.
Stop paying for GTM containers. UniPixel tracks server-side from the server you have.
```

**Sitelinks:** Campaign-level (shared across all ad groups). See campaign sitelinks below.

**Callouts (max 25 chars each):**

```
No GTM Container Needed
No Cloud Hosting Bills
Built Into WordPress
Your Server Does It All
5 Ad Platforms Built In
WooCommerce Ready
Auto Deduplication
Setup Takes 2 Minutes
```

**Key differentiators vs Stape:**
- Stape's entire model is hosting GTM server containers — UniPixel eliminates the container entirely
- Stape assumes GTM expertise (tags, triggers, variables) — UniPixel handles it inside WordPress
- Stape is another moving part that can break, scale unexpectedly, or change pricing — UniPixel runs on what you already have
- If Stape has an outage or you stop paying, your tracking stops — UniPixel runs on your server
- UniPixel does custom events from the WordPress admin — Stape needs GTM tag configuration for every custom event
- Stape supports non-WordPress platforms (Shopify, custom) — that's where they win, not WordPress

---

#### Ad group: Conversios

**Angle:** No GTM server containers, no cloud infrastructure, no complexity. Conversios requires a GTM server container for server-side tracking ($100–150/mo hosting, 15–20 hours setup, ongoing maintenance).

**Keywords (all exact match):**

```
[conversios]
```

**Headlines (max 30 chars each):**

```
Conversios Alternative
No GTM Container Needed
No Server Infrastructure
Stop Paying $150/mo Hosting
5 Platforms — One Plugin
Server-Side Without GTM
No Cloud Setup Required
UniPixel — Install and Go
CAPI Without Infrastructure
WooCommerce Tracking — Sorted
Your Server Already Does This
Skip the Container Setup
No 20-Hour GTM Config
Built Into WordPress
Setup Takes 2 Minutes
```

**Descriptions (max 90 chars each):**

```
Conversios needs a GTM server container. UniPixel tracks server-side from your own server.
Meta, TikTok, Google, Pinterest & Bing — no cloud hosting, no GTM, no container to manage.
Stop paying $150/mo for server infrastructure. UniPixel uses the server you already have.
No GTM expertise needed. No container provisioning. Just install and track. Five platforms.
```

**Status:** LIVE — 24 March 2026.

---

#### Ad group: PixelCat

**Angle:** More platforms, not just Meta. PixelCat is Meta-heavy — UniPixel covers all 5.

**Keywords (all exact match):**

```
[pixelcat]
```

**Headlines (max 30 chars each):**

```
PixelCat Alternative
More Than Just Meta
5 Platforms — One Plugin
TikTok + Pinterest Included
Server-Side Tracking Built In
Google + Bing Included Too
UniPixel — Install and Go
All Platforms, One Dashboard
CAPI Across 5 Platforms
WooCommerce Tracking — Sorted
Setup Takes 2 Minutes
Consent Management Built In
Deduplication Automatic
Track Every Conversion
Full Server-Side Included
```

**Descriptions (max 90 chars each):**

```
PixelCat does Meta. UniPixel does Meta, TikTok, Google, Pinterest and Bing. All in one.
Server-side tracking across 5 platforms with automatic deduplication. Install and go.
Meta, TikTok, Google, Pinterest & Bing — server-side tracking from your own server.
Stop installing separate plugins per platform. UniPixel handles all five in one place.
```

**Status:** LIVE — 24 March 2026.

---

#### Ad group: Pixelavo

**Angle:** Proven, full-featured alternative with all 5 platforms and server-side built in.

**Keywords (all exact match):**

```
[pixelavo]
```

**Headlines (max 30 chars each):**

```
Pixelavo Alternative
5 Platforms — One Plugin
Server-Side Tracking Built In
CAPI Across All 5 Platforms
TikTok + Pinterest Included
UniPixel — Install and Go
Proven WooCommerce Tracking
Consent Management Built In
Google + Bing Included
Deduplication Automatic
Setup Takes 2 Minutes
All Platforms, One Dashboard
Track Every Conversion
Full Server-Side Included
WooCommerce Tracking — Sorted
```

**Descriptions (max 90 chars each):**

```
Meta, TikTok, Google, Pinterest & Bing — server-side tracking from your own server.
Server-side tracking across 5 platforms with automatic deduplication. Install and go.
Five platforms, one plugin. No extra servers, no cloud hosting, no complexity.
All server-side, all self-hosted, all from one WordPress plugin. UniPixel. Install and go.
```

**Status:** LIVE — 24 March 2026.

---

#### Ad group: Meta Pixel WordPress

**Angle:** Meta's own pixel is buggy, clunky, and limited to one platform. People searching "meta pixel wordpress" are trying to install Meta's JS snippet — intercept them with something better.

**Keywords (all phrase match — broader than exact to catch variants):**

```
"meta pixel wordpress"
"meta pixel plugin wordpress"
"meta pixel wordpress plugin"
"meta pixel code wordpress"
"meta pixel for wordpress"
"wordpress meta pixel"
```

**Headlines (max 30 chars each):**

```
Meta Pixel — Buggy & Limited
Buggy. Clunky. Just Meta.
Meta Pixel? It's 2026.
Clunky Pixel Code? Ditch It
Your Pixel Is Holding You Back
UniPixel — Install and Go
5 Platforms — One Plugin
Server-Side CAPI Built In
Meta + TikTok + Google + More
WooCommerce Events Automatic
Setup Takes 2 Minutes
One Plugin. Five Platforms.
More Than a Buggy Pixel
Don't Stop at Just Meta
Track Everything Server-Side
```

**Descriptions (max 90 chars each):**

```
Meta's own plugin is rated 2.7 stars. UniPixel handles Meta CAPI properly — plus four more.
One plugin per platform? Five setups, five conflicts. UniPixel does all five in one install.
Events doubling? Purchases vanishing? UniPixel deduplicates automatically across all five.
Meta's plugin breaks with caching and consent tools. UniPixel works with both out of the box.
```

**Callouts (max 25 chars each):**

```
5 Platforms Not Just Meta
Server-Side CAPI Built In
No Pixel Code to Paste
WooCommerce Ready
Auto Deduplication
Consent Built In
Setup Takes 2 Minutes
Meta + TikTok + Google
```

**Status:** LIVE — 24 March 2026.

---

#### Competitor ad group matrix

| | PixelYourSite | Stape | Conversios | PixelCat | Pixelavo |
|---|---|---|---|---|---|
| **Type** | WordPress plugin | Infrastructure/hosting | WordPress plugin | WordPress plugin | WordPress plugin |
| **Their price** | $359/yr | ~$20/mo | $250–499/yr | Free/Pro | Free/Pro |
| **Primary attack** | Price + complexity + upsells | Unnecessary infrastructure | GTM container requirement | Limited platforms | Less proven |
| **Secondary attack** | Broken updates, ignored support | GTM expertise required | Invisible scaling costs | Meta-focused | Limited docs |
| **UniPixel advantage** | All 5 platforms included, no upsells | No container, no GTM, no cloud | No infrastructure, uses your server | 5 platforms vs limited | Established, all platforms |
| **Keyword strategy** | Bare brand (WP plugin, close variants cover modifiers) | Modifiers only (high volume, cross-platform — bare catches doc-seekers) | Bare brand (small WP plugin) | Bare brand (small WP plugin) | Bare brand (small WP plugin) |
| **WordPress-specific filter needed?** | No (WP plugin already) | Yes (cross-platform brand) | No (WP plugin already) | No (WP plugin already) | No (WP plugin already) |
| **Status** | LIVE | LIVE | LIVE | LIVE | LIVE |

---

### Campaign 4: Google Ads — Universal Category Keywords (LIVE)

**Technique:** Sweep and Strike / category interception
**Channel:** Google Search Ads (Search only — Display Network and Search Partners OFF)
**Targeting:** People searching for a solution, not a specific competitor
**Landing page:** https://unipixelhq.com
**Google Ads campaign name:** UniPixel | Universal | Sweep and Strike
**Status:** Live as of 24 March 2026. "Bid strategy learning" phase. Zero impressions — same verification blocker as Campaign 3. Verification completed 24 March.

#### Campaign settings

| Setting | Value |
|---|---|
| Campaign type | Search (not Smart, not Performance Max) |
| Networks | Search only — Display Network OFF, Search Partners OFF |
| Bidding | Maximise clicks |
| Daily budget | A$5.00 |
| Locations | All countries and territories |
| Languages | English |
| AI Max | OFF |

#### Emotional arc for all ad copy

All headlines and descriptions follow the **Opportunities → Worry → Pain → Solution → Reassure** framework:
1. **Opportunity** — what they could have (better data, better results)
2. **Worry** — plant the seed (are you missing conversions?)
3. **Pain** — the actual problem (data blocked, ad spend wasted)
4. **Solution** — UniPixel does this
5. **Reassure** — easy, quick, automatic, built in

#### Sitelinks

Same campaign-level sitelinks as Campaign 3.

---

#### Ad group: Server-Side Tracking

**Keywords (phrase match):**

```
"server side tracking wordpress"
"server side tracking woocommerce"
"conversions api wordpress plugin"
"capi wordpress plugin"
"server side pixel wordpress"
```

**Headlines (max 30 chars each):**

```
Better Data. Better Results.
Are Your Conversions Missing?
Your Ad Platforms Can't See All
UniPixel — Server-Side Done
5 Platforms — One Plugin
Setup Takes 2 Minutes
WooCommerce Events Automatic
No GTM Container Needed
Your Server Already Does This
UniPixel — Install and Go
Trusted by WooCommerce Stores
Deduplication Built In
Consent Management Included
See What Your Ads Really Do
No Cloud Hosting Required
```

**Descriptions (max 90 chars each):**

```
Your ad platforms could be seeing every conversion — but browsers are blocking the data.
Missing conversions means wasted ad spend and algorithms optimising on incomplete numbers.
UniPixel sends data server-side from your WordPress server to all five platforms. One plugin.
Install in 2 minutes. WooCommerce events fire automatically. Dedup and consent built in.
```

---

#### Ad group: WooCommerce Tracking

**Keywords (phrase match):**

```
"woocommerce tracking plugin"
"woocommerce conversion tracking"
"woocommerce pixel plugin"
"woocommerce server side tracking"
"woocommerce facebook pixel plugin"
```

**Headlines (max 30 chars each):**

```
Every Sale Reported Accurately
Is Your Store Losing Data?
Purchases Vanish Before Meta
UniPixel for WooCommerce
5 Ad Platforms — One Plugin
Purchases Track Automatically
Server-Side — More Accurate
UniPixel — Install and Go
No Setup Per Platform Needed
Meta + TikTok + Google + More
AddToCart to Purchase — Done
Setup Takes 2 Minutes
Consent Management Included
WooCommerce Stores Trust This
Auto Deduplication Built In
```

**Descriptions (max 90 chars each):**

```
Your store makes sales your ad platforms never see. That costs you real money.
Ad blockers and iOS privacy silently stop conversions reaching your ad platforms.
UniPixel sends every WooCommerce event server-side to five platforms automatically.
Install in 2 minutes. Purchase, AddToCart, Checkout — automatic. Dedup built in.
```

---

#### Ad group: Platform-Specific CAPI

**Keywords (phrase match):**

```
"facebook conversions api woocommerce"
"woocommerce facebook capi plugin"
"woocommerce meta pixel plugin"
"woocommerce tiktok pixel plugin"
"tiktok events api wordpress"
"google analytics woocommerce server side"
"pinterest conversions api wordpress"
```

**Headlines (max 30 chars each):**

```
All Five APIs — One Install
Still Setting Up One API?
One Platform Done. Four to Go.
UniPixel — CAPI Made Simple
5 Platforms — One Plugin
Meta + TikTok + Google + More
Server-Side From WordPress
CAPI Without the Headache
UniPixel — Install and Go
WooCommerce Events Automatic
Pinterest & Bing Included
Deduplication Built In
Consent Management Built In
Setup Takes 2 Minutes
Thousands of Stores Use This
```

**Descriptions (max 90 chars each):**

```
You could be sending server-side data to five ad platforms — not just the one you came for.
Setting up each platform's API separately is slow, fragile and easy to get wrong.
UniPixel connects your WooCommerce store to Meta, TikTok, Google, Pinterest & Bing at once.
One install. Automatic events. Built-in dedup and consent. Your server handles everything.
```

---

This is where volume opens up — much bigger search pool than competitor brand terms.

#### Learnings from setup

- Google's onboarding aggressively tries to broaden targeting (AI Max, Display Network, broad match, keyword suggestions) — refuse all of it for precise campaigns
- Exact match `[brackets]` are essential — without them Google interprets keywords loosely and burns budget on irrelevant searches
- Business name (Buildio) and billing entity (Elure Pty Ltd) are separate — billing entity can be updated when company name changes
- Google Ads recommended budget was A$0.37/day — the market for PYS competitor terms is genuinely small, confirming the sniper approach is correct
- Search is more precise than social marketing — lower volume but every click carries real intent
- **Advertiser verification silently blocks ad serving (discovered 24 March 2026, NOT resolved until 6 April 2026):** Google allows full account setup, campaign creation, billing, and shows "Eligible" status on everything — but serves zero impressions until advertiser identity verification is completed. "Eligible" is misleading — it means the ad passed policy review, NOT that it will be served. There IS a banner ("some ads may be paused or limited") but it uses ambiguous language and doesn't flag which campaigns are affected. Found under Billing → Advertiser verification. Three tasks needed: (1) answer questions about your organisation, (2) submit identity documents, (3) "Submit client documents" — this is MISLEADINGLY NAMED. For solo advertisers (not agencies), it just requires selecting your own verified payment profile and clicking Finish. It does NOT require separate client paperwork. On March 24 we believed verification was complete — it was not. Task 3 remained open and silently blocked Campaign 3 (Perch and Poach) for 3 full weeks with zero impressions, zero notifications, and zero campaign-level warnings. Finally resolved 6 April 2026. **If you ever create a new Google Ads account: (1) complete ALL verification tasks BEFORE creating campaigns, (2) confirm 3/3 tasks show green checkmarks, (3) the "Submit client documents" task applies even to non-agency accounts — just select your own profile.**
- **~~Competitor brand keywords have near-zero search volume~~ WRONG — this conclusion was invalid (corrected 6 April 2026):** All 5 competitor ad groups (PYS, Stape, Conversios, PixelCat, Pixelavo) had 0 impressions over 9 days — but this was caused by advertiser verification silently blocking ALL ad serving on Campaign 3, NOT by low search volume. The "near-zero volume" conclusion was drawn from zero data. Verification was NOT actually complete on March 24 — the 3rd task ("Submit client documents") remained open until April 6. That task was misleadingly named; it only required selecting the existing verified payment profile and clicking Finish. Stape is a major player in server-side tracking with real search volume. The actual search volume for these competitor keywords is UNKNOWN — it has never been tested with ads that were allowed to serve. Monitor all 6 ad groups now that verification is genuinely complete.
- **Use phrase match for category keywords:** Campaign 3 used exact match (brackets) for competitor names. Campaign 4 and the Meta Pixel ad group use phrase match (quotes) — catches longer-tail variants like "how to install meta pixel on wordpress" while staying relevant. Phrase match is better for category-level keywords where close variants matter.
- **Meta Pixel ad group targets Meta's own pixel, not competitors:** The "perch" is on Meta's own clunky pixel snippet. The attack angle: Meta's pixel is buggy, clunky, and limited to one platform. UniPixel does Meta properly plus four more. Do NOT claim Meta lacks server-side (CAPI exists) — the weakness is setup complexity, the official plugin being rated 2.7 stars, and single-platform limitation.

---

### How the 4 campaigns work together

```
Campaign 1 (Forums)          → builds credibility, seeds awareness, collects Pinpoint hooks
Campaign 2 (Meta/Insta)      → graphic-driven brand awareness + retargeting in PYS ecosystem
Campaign 3 (Google - Perch)  → captures competitor-name searches (volume unknown — was blocked by verification until 6 Apr 2026)
Campaign 4 (Google - Universal) → captures category searches (main volume driver)

     Forums (organic)  →  "Who is UniPixel?"  →  Google search  →  Campaign 4 captures them
     Meta ad (graphic)  →  "Interesting..."    →  Google search  →  Campaign 4 captures them
     Google C3 (competitor) → Landing page     →  Install        →  Convert (volume TBD — was blocked by verification)
     Google C4 (universal)  → Landing page     →  Install        →  Convert (main channel)
```

> **All four campaigns feed each other.** Forum presence creates brand recognition. Meta/Insta graphics create curiosity. Campaign 3 catches the rare competitor-name searcher. Campaign 4 catches the much larger pool of people searching for a solution by category ("woocommerce tracking plugin", "server side tracking wordpress"). Campaign 4 is the main volume driver — Campaign 3 is the sniper complement.

---

## UniPixel Independent Web Presence

> **Updated 31 March 2026.** UniPixel is now its own independent marketing entity with its own domain, social presence, and identity. Buildio remains the parent company for billing and business operations but is not customer-facing.

### unipixelhq.com — The Hub

UniPixel's primary web presence is **unipixelhq.com** (previously buildio.dev/unipixel/). This is the hub for everything customer-facing: landing pages, documentation, blog content, video, tracking setup guides, and all inbound links from ads and social.

All campaigns, ad landing pages, sitelinks, and content should point to unipixelhq.com. The buildio.dev/unipixel/ URL should redirect to unipixelhq.com.

### Meta Account Structure

```
Business Portfolio: Buildio (back-office — owns ad accounts, handles billing)
  └─ Ad Account: Buildio (ID: 931369629429092) — rename to "UniPixel" (see below)
      └─ Campaign 2: Meta/Instagram graphics
      └─ Any future Meta ad campaigns
  └─ Page: UniPixel (customer-facing — ads run "from" this page)
```

**How this works:** Buildio is the business entity behind the scenes. It owns the ad account and handles billing (Elure Pty Ltd). The UniPixel Facebook Page is what the public sees — when ads run, they show "UniPixel" as the advertiser. Users never see "Buildio" in any customer-facing context.

**Ad account rename:** The ad account is currently named "Buildio" — this is only visible internally in Ads Manager but should be renamed to "UniPixel" for clarity. The Business Portfolio stays as Buildio (that's the company). Rename in: Ads Manager → Ad account settings → Ad account name.

---

### Facebook Page — Communications & Advertising Pillar

> **Status as of 31 March 2026:** Shell page exists. Needs branding, content, and activation before further ad spend scales.

#### Why the Facebook page matters

The UniPixel Facebook page is not optional social media — it's infrastructure that directly affects ad performance, SEO, and trust.

**1. Ad effectiveness.** Meta's ad delivery system evaluates the whole advertiser profile. An ad running from a shell page with no posts, no followers, no activity gets treated as lower quality. Users who see the ad and check the page bounce when it's empty. Both hurt cost per result and delivery reach. The current $0.03/click could degrade as spend scales if the page stays dead.

**2. Trust checkpoint.** When someone sees a UniPixel mention in a forum, a Google ad, or a Reddit reply — some will search "UniPixel" on Facebook/Instagram. A branded, active page with recent posts confirms legitimacy. A dead shell page raises doubt. This is the credibility loop that Campaign 1 (forums) and Campaign 3 (Google Ads) depend on.

**3. SEO backlinks and domain authority.** A Facebook page with unipixelhq.com linked in the bio and in posts is a link from a DA 96 domain. Not a magic SEO bullet, but a foundational signal Google expects to see for a real business. Every social profile (Facebook, X/Twitter, LinkedIn, YouTube) pointing to unipixelhq.com builds domain authority and brand signal consistency.

**4. Retargeting seed.** Page engagement (likes, comments, video views, post interactions) builds custom audiences for retargeting. Every organic interaction is future ad targeting data — people who engaged with UniPixel content can be retargeted with Campaign 2 graphics or direct-response ads.

**5. Boosted posts = low-friction ads.** A post about a real tracking problem, boosted to WooCommerce/ecommerce interest audiences, IS a Pinpoint and Panorama or Sweep and Strike ad in organic form. Lower friction than building full Figma creatives. This can supplement or even bootstrap Campaign 2 before dedicated graphics are ready.

#### Minimum viable page — do this first

Before any more ad spend scales up, the page needs to look real:

- **Profile picture:** UniPixel logo
- **Cover image:** Branded header (can match unipixelhq.com hero)
- **Page bio/description:** One-line value prop + link to unipixelhq.com
- **Website link:** unipixelhq.com
- **Category:** Software / Technology
- **3–5 initial posts** to populate the feed (version release, tracking tip, link to a unipixelhq.com article)
- **CTA button:** "Learn More" → unipixelhq.com

#### What to post (ongoing, low effort)

| Post type | Frequency | Example |
|---|---|---|
| Version release notes | Each release | Rewritten as customer benefits per changelog tone rules — not dev notes |
| Tracking tips / education | 1–2x/week | "Did you know iOS privacy blocks up to 30% of conversions from reaching Meta? Server-side tracking changes that." |
| Links to unipixelhq.com articles | As published | Share with a hook line, not just the URL |
| Myth vs reality | Occasional | Sweep and Strike format works natively as social content |
| Milestone / social proof | When earned | "6 five-star reviews" — honest, no inflation |

**Boosting:** Any post that performs well organically should be boosted. Target: WooCommerce, Facebook Pixel, conversion tracking, digital advertising interests. Even $5–10/boost extends reach significantly and feeds the retargeting audience.

#### The compound flywheel

```
Active page → better ad delivery → more engagement → more profile visits
  → more clicks to unipixelhq.com → more authority signals → better SEO
    → more organic installs → more reviews → better WP.org ranking
      → more installs → more content to post about → more page activity
```

The Facebook page is one of the cheapest pieces to activate in this loop. Everything else (ads, SEO, installs) benefits from it being alive.

---

## The 5 Sales Pillars — Why UniPixel

> **Updated 31 March 2026.** These are the five customer-facing reasons someone should use UniPixel. Not technical features — actual outcomes a store owner cares about. Every piece of content (ads, Facebook posts, landing pages, readme, forum replies) should map to one or more of these pillars. The through-line across all five: **you're already spending money on ads — UniPixel makes sure that money works as hard as it can.**

### Why tracking matters at all

You're spending money on ads. Are they spending it well? Your ad platforms make automated decisions about who sees your ads, how much to bid, and where to allocate your budget — all based on the conversion data they receive. If that data is incomplete, every decision is wrong. You pay more to reach worse audiences, you kill campaigns that were actually working, and you scale campaigns that aren't. You're losing money you can't see — because the data that would show you is never arriving.

### Pillar 1: Your ads are wasting money you can't see

Ad blockers, iOS privacy, and browser restrictions silently hide your real sales from your ad platforms. Their algorithms optimise on incomplete data — so they find worse audiences and spend your budget on people who won't buy. UniPixel gets your real results through — directly from your server, bypassing everything that blocks browser-based tracking.

**The outcome:** Your ad platforms see more of your real conversions. Their algorithms make better decisions. Your cost per customer comes down. Your ad spend works harder.

### Pillar 2: No extra infrastructure. No extra bills.

Other solutions force you into $100–150/mo cloud servers and complex GTM container setups just to send conversion data. UniPixel works from your existing WordPress hosting — because your server can already do this. Nothing extra to build, manage, or pay for.

**The outcome:** Server-side tracking without the server. No cloud hosting to provision, no GTM expertise to learn, no infrastructure bills that grow with your traffic. It runs on what you already have.

### Pillar 3: Track the actions that drive your business

Not just page views and purchases — track the button clicks, form submissions, and interactions that actually matter to your business. Build smarter ad audiences based on what people do on your site, not just that they visited.

**The outcome:** Your ad platforms learn which actions lead to sales. They find more people who take those actions. Your campaigns get smarter without you touching them.

### Pillar 4: Privacy and consent without the headache

GDPR, cookie laws, consent management — UniPixel handles it. Works with 9 major consent platforms or its own built-in popup. Data only sends when visitors consent. No legal grey areas, no compliance anxiety.

**The outcome:** You stay compliant without becoming a privacy lawyer. Consent is checked before any data fires. You track responsibly and your visitors' choices are respected automatically.

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

## Foundation — Internal Reference

> **Everything below is the strategic foundation built through iterative refinement. Every correction, language rule, and positioning decision is captured here. This document is the source of truth for all marketing content.**

---

## The Industry Problem

> **This is the peg. Everything hangs off this.**

### PROBLEMS / OPPORTUNITIES

**1. Browser delivery is failing.**

Advertising platforms need better data. The browser — the only way most sites have ever sent it — is increasingly unable to deliver it. The delivery mechanism is failing, resulting in inaccurate conversion reporting, inaccurate measurement, and reduced algorithm effectiveness. Platforms are crying out for more and better data. They've invested significantly in server-side protocols (Meta Conversion API, Google Measurement Protocol, TikTok Events API) specifically to solve this. If you're not using them, you're on the back foot in sales and marketing.

**2. Tracking is event-driven now — not just page views.**

Platforms are hugely event-driven now. Not just page views — clicks, form submissions, interactions, purchases, add-to-carts. Google Tag Manager exists to manage this but requires GTM expertise. Most site owners can't manage that.

**3. Custom events need to be simple.**

Customers need a simple way to track their own events — this button, that form, that popup. They need an interface to set them up and manage them without technical complexity or a developer.

**4. Consent is non-negotiable.**

Don't send data when customers haven't consented. The industry is cracking down. Real compliance risk. Real fear point.

### THE WORDPRESS ADVANTAGE — WHY CONTAINERS DON'T APPLY

> **This is one of UniPixel's most powerful selling points. Use it everywhere — ads, content, landing pages, forum replies.**

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

**How to use this:**
- In ads: "Your WordPress server already does this — you just need the plugin"
- In content: explain WHY containers exist (for platforms that can't run server code), then show WordPress is different
- In forum replies: when someone asks about server-side tracking setup complexity, point out they're on WordPress and it's simpler than they think
- In competitor comparisons: don't say containers are bad — say they're unnecessary for WordPress

---

### HOW UNIPIXEL ADDRESSES ALL FOUR

1. **Server-side delivery.** Sends data through the protocols platforms have built — directly from the WordPress server.
2. **Event-driven tracking.** Handles WooCommerce events automatically and doesn't require GTM.
3. **Custom events.** Interface inside WordPress to set up and manage custom event triggers — buttons, forms, interactions. No GTM, no code.
4. **Consent.** Checks consent before any event fires. Reads from six CMPs or its own built-in popup. Doesn't send data without permission.

---

## The Solution

Send data through the server-side protocols platforms have built — more comprehensively, with better coverage, and more responsibly. The result: more accurate conversion reporting, better measurement, stronger algorithm performance, more effective ad spend.

UniPixel makes this accessible by handling the technical complexity inside WordPress — simple setup, and your server starts delivering data through those protocols directly to the platforms.

---

## Key Language Rules

> **These are non-negotiable. They apply to all content — copy, ads, readme, blog posts, videos, everything.**

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

**Competitive is three market differentiators — not five. The original five included "price wall" and "feature paywall" which both lean on "free" as the selling point. That stops working when Pro launches. Dropped.**

---

## Pillar 1: Universal Messaging

> **What this is:** Copy for ads, graphics, social, and content. These are for people who don't know they have a problem. The hooks need to land in their world — ads, spending money, seeing results — not in technical tracking language. Someone scrolling Meta or Google doesn't know what "tracking" means in this context.

> **Hook guidance:** 3–6 words. Fear, curiosity, or discomfort. Not a feature, not a solution, not good news. The hook is the scary situation they're in. The context (6–12 words) then puts it in their frame — what's actually happening to them. Strip out unnecessary sentence structure builders. "Your real results are being hidden" not "Ad blockers and privacy settings are hiding your real results from the platforms you pay for."

> **Format note:** Hook/context/follow-up format is for customer-facing ad copy only. Internal concepts and competitive differentiators don't need this format. Concepts don't need hooks.

### Ad Techniques — Two Directions

> **All UniPixel ads and content use one of two directional techniques. They are opposites. Choosing the right one depends on where the audience is — do they already have a specific problem, or do they not yet know something is wrong?**

| Technique | Direction | How it works | When to use | Established roots |
|---|---|---|---|---|
| **Pinpoint and Panorama** _(alias: Spark and Bang)_ | Narrow → Wide | Lead with a hyper-specific micro-frustration sourced from real forums. The specificity creates trust ("how did they know?"), then the lens opens to reveal UniPixel solves the whole category. | They already have a specific problem. They're in the forum right now. They just saw the error. | Collier's "enter the conversation" + Kennedy's "Find Yourself Here" + specificity-credibility principle (Halbert, Schwartz) |
| **Sweep and Strike** | Wide → Narrow | Lead with a broad, universal fear that hits everyone ("your ad data is wrong"). Agitate the consequences. Then strike with UniPixel as the one sharp answer. | They don't know they have a problem yet. Education-first. Awareness-stage. | Classic PAS (Problem-Agitation-Solution) — the most proven direct response formula |
| **Perch and Poach** _(metaphor: busking outside the concert)_ | Beside → Away | Place ads and presence exactly where competitor users already congregate — search terms, support forums, community spaces. Intercept users who are already experiencing subconscious pain with the competitor, offer them a better path. | They're already using a competitor. They're not looking for an alternative — but they're experiencing friction they've normalised. | Competitive proximity / conquest marketing — standard in SaaS (Salesforce vs HubSpot, Slack vs Teams). The twist: targeting subconscious pain, not conscious dissatisfaction. |

> **How they pair:** Sweep and Strike creates demand (Universal pillar). Pinpoint and Panorama converts people who are already experiencing the problem (works across both pillars). Perch and Poach intercepts users who are locked into a competitor but experiencing friction they haven't named yet (Competitive pillar). A campaign can run all three simultaneously — Sweep and Strike for cold audiences, Pinpoint and Panorama for forum outreach and retargeting, Perch and Poach for competitor audiences.

---

### Problem Area 1: Missing Data

| | Pain | Rainbow |
|---|---|---|
| **Hook** | Your ad data is wrong. | See what's really happening. |
| **Context** | Your real results are being hidden from the platforms you pay for. | Data sent directly from your server — bypassing everything that blocks it. |
| **Follow-up** | You're making budget decisions based on numbers that are missing pieces. Ad blockers, iOS privacy and browser restrictions quietly stop your conversion data from reaching your ad platforms. No errors, no warnings — just less than what actually happened. | Your server knows every conversion. UniPixel sends that data straight to Meta, Google and TikTok. No browser in the middle. You get a more accurate picture of what your ads deliver — and you stop making decisions on incomplete numbers. |

> **Why "Your ad data is wrong" works:** It's fear. It's personal ("your"). It's about something they care about (ad performance, money). It doesn't mention tracking, pixels, server-side, or any technical concept. It makes them feel like something is broken that they didn't know about. Previous attempts like "Your tracking has blind spots" or "You're getting more sales than your ads report" failed — the first is technical jargon, the second is actually good news.

### Problem Area 2: Weaker Ad Performance

| | Pain | Rainbow |
|---|---|---|
| **Hook** | Your ads cost more than they should. | Spend less for the same results. |
| **Context** | Your platforms can't see your real results — so they waste your budget on the wrong people. | More accurate data means your platforms find better customers for less money. |
| **Follow-up** | Your ad platforms only see a fraction of your real results. They pull budget from what's working and shift it to what isn't. You're paying more to reach worse audiences — not because your ads are bad, but because the data feeding them is incomplete. | When more of your results reach your ad platforms, the algorithm learns who actually converts. It stops wasting your budget on the wrong audiences. Your cost per result comes down — because the data got more accurate, not because you changed your ads. |

> **Why this is separate from Problem Area 1:** Problem Area 1 is "your data is wrong." Problem Area 2 is "that's why your ads cost too much." The first is the technical reality. The second is the financial consequence they feel in their wallet. Both hooks ended up in the same place in early drafts — the fix was making the second one about money and cost, not data.

> **Pain points under these two areas (the consequences people actually feel):**
> - Am I spending my ad budget effectively?
> - Am I wasting money without knowing it?
> - Could my ads be performing better and I just can't see how?
> - What do I need to do to improve this?
>
> The fear isn't "I'm losing money." The fear is "I don't know if I'm getting the most out of what I'm spending — and I don't know what to do about it."
>
> And the answer to "what do I do" is simple: UniPixel.

### Ad Technique: Pinpoint and Panorama _(internal alias: Spark and Bang)_

> **What this is:** A different ad format to Problem Areas 1 and 2. Instead of leading with a broad pain ("your ad data is wrong"), Pinpoint and Panorama ads lead with a hyper-specific micro-frustration — something so precise that the viewer thinks "wait, that's exactly what I'm dealing with — how did they know?" That specificity is the trust signal. It creates a "huh → ah → wow" arc: recognition → curiosity → expansion into UniPixel as the solution to a whole world of problems they didn't know had a single answer.
>
> **Direction:** Narrow → Wide. Start at a single sharp point, expand to the full solution landscape.
>
> **Established roots:** Robert Collier's "enter the conversation already in the customer's mind" + Dan Kennedy's "Find Yourself Here" technique + the specificity-credibility principle (Halbert, Schwartz). The hyper-specific detail acts as a recognition hook — it bypasses scepticism because it cannot be dismissed as generic marketing. The psychological arc is: inverse Barnum Effect (genuine specificity > vague relatability) → ethical cold reading ("how did they know?") → pattern interrupt via detail.

> **How it works:** The **Pinpoint** is one acute, ground-level detail — a specific screen they're staring at, a specific error they just saw, a specific task they're stuck on. It's sourced from real forum posts, support threads, and community questions. The **Panorama** is the pull-back: the lens opens from that one frustration to reveal that UniPixel solves not just that problem, but the entire category of problems around it.

> **Where to source Pinpoints:** WordPress.org support forums (Meta for WooCommerce, Conversios, PixelYourSite), Reddit (r/woocommerce, r/PPC, r/FacebookAds), Facebook groups. Look for the exact words people use when they're stuck — those words become the hook.

> **Format:** Pinpoint (question or statement — 3–8 words, uses their exact language) → Panorama (reframe + expand — the frustration is a symptom of a bigger problem UniPixel solves)

| # | Pinpoint | Panorama |
|---|---|---|
| 1 | "Advanced Matching — what?" | Struggling to navigate the conversions world? UniPixel handles it. Advanced Matching, server-side tracking, event deduplication — all set up, all automatic. |
| 2 | "Why does Meta say I have 12 purchases but WooCommerce says 47?" | Your pixel can only see what the browser lets through. UniPixel sends every conversion directly from your server — no browser in the middle. |
| 3 | "I set up CAPI and my events are doubling" | That's a deduplication problem. UniPixel matches event IDs automatically so platforms count each conversion once — not twice. |
| 4 | "My Event Match Quality score is stuck at 'Poor'" | Meta can't match your events to real people because the browser strips out the data it needs. UniPixel sends user data server-side — your match quality goes up without you touching anything. |
| 5 | _(source more from forums — real language, real frustrations)_ | |

> **Why this works differently to Problem Areas 1 & 2:** Problem Areas are broad fear-based hooks — they work on people who don't know they have a problem. Pinpoint and Panorama works on people who are **currently experiencing** a specific problem. They're in the forum right now. They just saw the error. The specificity bypasses scepticism — it feels like someone who understands, not someone selling. The expansion from "I can fix this one thing" to "actually I fix everything in this space" is where the conversion happens.

> **Using Pinpoints in forums:** The same Pinpoint hooks that work as ad copy also work as forum responses. Find the thread where someone posted the exact frustration, answer it helpfully, and then mention UniPixel as the thing that handles it. Pinpoint and Panorama is both an ad format and an outreach strategy.

### Ad Technique: Perch and Poach _(metaphor: busking outside the concert)_

> **What this is:** Competitive proximity advertising. Instead of creating demand or answering existing pain, you position UniPixel exactly where competitor users already are — and intercept them. The metaphor: if PixelYourSite is the concert, you're the busker set up right outside the venue. The audience didn't come looking for you, but they walk past you on their way in and out. Some stop. Some listen. Some realise they prefer what you're playing.
>
> **Direction:** Beside → Away. You don't create the audience — you borrow it from the competitor and redirect it.
>
> **The psychological insight:** Users of competitors like PixelYourSite experience real pain — confusing UIs, aggressive upsells, broken updates, support friction — but they've **normalised** it. It's not front-of-mind. They're not searching "PixelYourSite alternative" because they don't think of the friction as a solvable problem. It's background noise in their workflow. But the pain is there, subconsciously, and when they see an ad that speaks to that exact friction at the exact moment they're engaging with the competitor ecosystem, it surfaces. That's the intercept.
>
> **Established roots:** Conquest marketing / competitive targeting is standard in SaaS (Salesforce bidding on "HubSpot CRM", Slack targeting "Microsoft Teams frustrations"). The UniPixel angle is sharper: we're not just bidding on a competitor name — we're targeting the **subconscious friction** their users experience but haven't articulated.

> **Where to perch (placement channels):**
> - **Google Ads** — bid on competitor brand terms: "PixelYourSite", "Conversios", "PixelYourSite alternative", "PixelYourSite problems". These searches have high intent and the user is already in the competitor ecosystem.
> - **Competitor support forums** — WordPress.org support threads for PixelYourSite, Conversios, Meta for WooCommerce. Users posting there are experiencing active friction. Be helpful first, mention UniPixel naturally.
> - **Reddit / Facebook groups** — threads where people complain about or ask for help with competitor plugins. Same approach: help first, position second.
> - **YouTube** — ads on competitor tutorial/review videos. Someone watching "How to set up PixelYourSite CAPI" is in the middle of the exact complexity UniPixel eliminates.

> **What to say (the poach):** The messaging is NOT "we're better than X." It's empathy for the friction they're experiencing, followed by a simpler path. The user shouldn't feel sold to — they should feel understood.

| # | Perch point | Poach message |
|---|---|---|
| 1 | Google Ad: "PixelYourSite" | "Server-side tracking shouldn't need a 40-step setup guide. UniPixel — install, connect, done." |
| 2 | Google Ad: "PixelYourSite alternative" | "Looking for something simpler? UniPixel sends conversion data to Meta, Google, TikTok and Pinterest — no server containers, no code." |
| 3 | Forum: user struggling with CAPI setup on competitor | Helpful reply explaining the concept, then: "If you want something that handles this automatically, UniPixel does CAPI + deduplication out of the box." |
| 4 | YouTube: pre-roll on competitor tutorial | "If this tutorial is 20 minutes long, the plugin is too complicated. UniPixel — server-side tracking in 2 minutes." |
| 5 | Reddit: "PixelYourSite keeps breaking after updates" | Empathise, explain why it happens, mention UniPixel as a lightweight alternative that doesn't break. |

> **How Perch and Poach differs from Pinpoint and Panorama:** Pinpoint and Panorama sources its hooks from forums but targets the **problem** (the frustration exists independent of any tool). Perch and Poach targets the **competitor's users specifically** — the placement is the strategy, not the message. You're not fishing in open water; you're fishing in their pond.
>
> **How Perch and Poach differs from Sweep and Strike:** Sweep and Strike educates cold audiences who don't know they have a problem. Perch and Poach targets warm audiences who are already in the space — they know about tracking, they have a tool, they just have the wrong one (or a harder one).
>
> **Key rule:** Never attack the competitor directly. Never say "PixelYourSite is bad." Always frame it as: "this problem you're having — there's a simpler way." The competitor's own complexity does the attacking for you.

---

### Perch and Poach: PixelYourSite — Competitive Intelligence & Strategy

> **Why PixelYourSite:** 18.9M total downloads. Most installed tracking plugin in the WordPress ecosystem. WordPress.org rating 4.3/5 (261 reviews) but **Trustpilot 2.3/5 with 75% one-star reviews.** The gap between install base and satisfaction is the opportunity.

#### What PixelYourSite offers (know the battlefield)

| Feature | Free | Pro / Add-on |
|---|---|---|
| Meta Pixel + CAPI | ✅ Free | ✅ |
| Google Analytics 4 | ✅ Free | ✅ |
| Google Tag Manager | ✅ Free | ✅ |
| Google Ads | ❌ | Pro |
| TikTok | ❌ | Pro |
| Pinterest | ❌ | Separate add-on |
| Microsoft/Bing UET | ❌ | Separate add-on (client-side only, no CAPI) |
| WooCommerce events | ✅ Free (basic) | ✅ (advanced) |
| Multiple pixels per platform | ❌ | Pro |

**Pricing (May 2025 restructure):**
- Starter: $359/year (1 site)
- Advanced: $399/year (10 sites, includes Pinterest + Bing add-ons)
- Agency: $999/year (100 sites, all add-ons)

**Key fact:** Meta CAPI is in their free version. "No CAPI" is NOT an angle against PYS. The angle is: everything beyond Meta + GA4 costs $359+/year.

#### Where UniPixel wins outright

| Area | PixelYourSite | UniPixel |
|---|---|---|
| Multi-platform (free) | Meta + GA4 only. TikTok, Pinterest, Bing = paid ($359+/yr) | All 5 platforms included |
| Microsoft CAPI | No — UET client-side only | Built (prototype) |
| Dashboard experience | Aggressive upsell nags, cluttered | Clean, no nags |
| Setup | "Navigate unclear settings and click non-intuitive buttons" | Install, connect, done |
| Support reputation | Trustpilot 2.3/5. Refunds ignored. Weeks without reply | — |
| Caching conflicts | Known architectural issue — cache can kill tracking silently | — |
| Thank-you page dependency | Purchase only fires if customer views thank-you page | Server-first fires regardless |

#### The 9 documented pain points (ranked by frequency)

**1. Support is dead.**
Weeks without replies. Refunds promised but ignored. 15 pages of unresolved WordPress.org support threads. Trustpilot: 2.3/5, 75% one-star. Direct quote: _"I cannot recommend this plugin anymore. I sent several emails and never received a proper answer. The plugin developer is not very helpful."_ Another: _"Customer support takes a very long time to respond. I requested a refund within the specified timeframe, but I have received no reply. This is the worst software I have ever used."_

**2. Updates break sites.**
v9.5.2 shipped PHP 8 warnings to production. v12.4.1 Pro froze entire sites — the plugin loaded external requests that locked browsers. Direct quote: _"After update Pro version to 12.4.1 I have problem to all my sites using PixelYourSite: Site freeze."_

**3. Fake conversion data — $40K loss.**
One user lost $40,000+ in ad spend because PYS sent phantom purchase events to Facebook. Public WordPress.org thread. Direct quote: _"I discovered that last month I spent over $40K on advertising for sales that never happened."_ This is the most damaging single complaint in the ecosystem.

**4. Aggressive upselling.**
Dashboard flooded with upgrade nags from installation. Direct quote: _"Plugins are spamming WordPress dashboard non-stop, bad UX for settings."_ Another: _"Upon installation, users encounter a flood of ads pushing to upgrade to the Pro version along with persistent notifications that clutter the dashboard."_

**5. Caching kills tracking silently.**
If a cache plugin serves a bot-generated page, PYS suppresses itself on that cached version. All real visitors then get the cached page — with no tracking. PYS's own documentation acknowledges this. There is no clean fix.

**6. Confusing setup.**
Multiple independent sources: _"The process is not straightforward, requiring users to navigate unclear settings and click non-intuitive buttons to access basic fields like entering a Meta Pixel ID."_

**7. Consent plugin conflicts.**
Complianz (popular GDPR plugin) blocks PYS even after visitors grant consent. Disabling Complianz fixes tracking but removes GDPR compliance. Lose-lose.

**8. Purchase tracking misses.**
Purchase event only fires when customer views the thank-you page. If they close the browser before it loads, the purchase is never tracked. Structural limitation.

**9. Price resistance.**
$359/yr minimum for full stack. 6+ piracy sites offer cracked Pro — a signal that users want the features but resist the price. The pricing restructure in May 2025 actually raised entry-level prices.

#### Perch and Poach execution plan — PixelYourSite

**Channel 1: WordPress.org support forum**
15 pages of unresolved threads. People are posting problems and getting silence. Show up, be helpful, mention UniPixel naturally.

Target thread types:
- CAPI not working / events doubling
- Update broke my site
- Consent plugin conflicts
- "Is there a simpler alternative?"
- Setup confusion / can't find where to enter Pixel ID

Approach: Answer the actual question first. Provide genuine value. Then: _"If you want something that handles this automatically, UniPixel does it out of the box — Meta, Google, TikTok, Pinterest and Bing, all free."_

**Channel 2: Google Ads — competitor brand terms**
- `"PixelYourSite"` — high intent, they're looking for it
- `"PixelYourSite alternative"` — they've already decided to leave
- `"PixelYourSite problems"` — they're in pain right now
- `"WooCommerce server side tracking"` — category search where PYS dominates results
- `"WooCommerce conversion tracking plugin"` — broader but high value

**Channel 3: Content / SEO**
- "PixelYourSite vs UniPixel" comparison page — control the narrative
- "PixelYourSite alternative for WooCommerce" — capture the exit traffic
- "How to avoid phantom conversions in WooCommerce" — targets the $40K story without naming PYS
- "WooCommerce server-side tracking without a $359/year plugin" — price pain

**Channel 4: Reddit / Facebook groups**
- r/woocommerce, r/PPC, r/FacebookAds — threads mentioning PYS frustrations
- WooCommerce Facebook groups — "what tracking plugin should I use?" threads
- Approach: peer recommendation tone, not sales pitch

**Channel 5: YouTube**
- Pre-roll ads on PYS tutorial videos (people watching 20-minute setup guides = complexity signal)
- Own content: "WooCommerce server-side tracking in 2 minutes" — implicit comparison through brevity

#### Sharpest hooks (for ads, forum replies, and content)

| Hook | Pain it targets | Source |
|---|---|---|
| "Tracking 5 platforms shouldn't cost $359/year" | Price shock — multi-platform paywall | PYS pricing page |
| "15 pages of unanswered support threads" | Abandonment anxiety — you're on your own | WordPress.org forum |
| "One plugin update shouldn't freeze your entire site" | Reliability fear — updates as risk | v12.4.1 incident |
| "Your tracking plugin sent sales that never happened" | Data trust destruction — the worst possible outcome | $40K WordPress.org thread |
| "You shouldn't need a 20-minute tutorial to enter a Pixel ID" | Complexity frustration — wasted time | Setup complaints |
| "Your cache is silently killing your tracking and you don't know it" | Invisible data loss — silent failure | Caching architecture bug |
| "Your purchase tracking only works if the customer waits for the thank-you page" | Missed conversions — structural weakness | Thank-you page dependency |
| "Dashboard full of upgrade nags before you've even set up a pixel" | Upsell fatigue — disrespect of attention | Installation experience |

> **Remember the key rule:** These hooks expose friction — they don't name-call. The framing is always "this shouldn't be this hard" or "there's a simpler way", never "PixelYourSite is bad." In forum replies, don't reference these hooks as attack lines — just be helpful and let the comparison speak for itself. In Google Ads and content, the hooks become headlines.

---

## Pillar 1: Universal Audiences

### U1: "I have a pixel and I think it's working fine"

The largest group by volume but least aware. They installed a pixel years ago, it fires, they see some conversions — so they assume it's working.

**Where they are:** General WooCommerce groups, WordPress communities, small business forums. Not searching for tracking solutions.

### U2: "Meta / Google keeps warning me about data quality"

They've seen the warnings in Events Manager or GA4. They know something's wrong but don't understand what server-side tracking is or why it matters.

**Where they are:** Searching "meta event match quality", "facebook pixel not tracking all conversions", "ga4 missing data."

### U3: "I know ad blockers are a problem but I don't know the solution"

They understand broadly that privacy changes and ad blockers affect tracking. But they haven't connected the dots to "I need server-side tracking."

**Where they are:** Ecommerce communities, PPC forums, reading blog posts about digital marketing trends.

---

## Pillar 2: Competitive Messaging

> **What this is:** Market differentiation. These people already have tracking, already know what server-side is, or have already tried a solution and it didn't work. They're not being educated — they're being given a reason to switch.

> **Format note:** Competitive differentiators are not customer-facing ad copy in the same way Universal is. They don't need hook/context/follow-up format. They show up in comparison pages, readme descriptions, feature lists, and content where someone is already evaluating. Concepts don't need hooks.

> **Why three, not five:** Earlier versions had five competitive pairs. Two of them (price wall, feature paywall) were built on "it's free" as the differentiator. That's a growth tactic, not a market position — it stops being true when Pro launches at $89/yr. The real competitive position must hold at any price point.

### C1: Simpler — no server container infrastructure

Most competing solutions that offer server-side tracking require you to set up and pay for a GTM server container. That means spinning up a separate cloud server (Google Cloud, AWS, etc.) at $100–150/month ongoing, 15–20 hours of technical configuration, and maintaining it — if it breaks, your tracking breaks. Conversios requires this. Stape's entire business model is hosting these containers for you.

The cost of this infrastructure is unpredictable and often invisible — it grows quietly, and if it breaks or the provider changes pricing, your tracking goes with it.

UniPixel skips all of that. Your WordPress server talks directly to Meta's API, Google's API, TikTok's API, Pinterest's API, Microsoft's API. No server container, no cloud proxy, no extra hosting, no developer. Everything runs on infrastructure you already control. No surprise costs. No invisible bills. No dependency on third-party hosting that can change under you.

### C2: Self-Hosted

Some solutions route data through their own servers (SweetCode Cloud, Stape hosting). Your data passes through a third party. If they raise prices, go down, or shut down — your tracking breaks. UniPixel fires directly from your WordPress server to platform APIs. No middleman. No vendor dependency. If your site is online, your tracking is online.

### C3: It Works

Meta for WooCommerce is the obvious first choice — official and free. But it's rated 2.2/5 stars with 308 one-star reviews. It breaks sites, conflicts with plugins, and only covers Meta. The paid alternatives work but come with cost and complexity. UniPixel covers five platforms, doesn't break sites, doesn't require external infrastructure.

---

## Pillar 2: Competitive Audiences

### C1: "Meta told me to set up CAPI — what plugin do I use?"

They've accepted they need CAPI. Now they're shopping. They've found PixelYourSite ($359/yr), Conversios ($499/yr), maybe Pixel Manager ($19/mo). They're comparing features and prices.

**Where they are:** Searching "best meta conversion api plugin woocommerce", "pixelyoursite vs conversios", "free capi plugin wordpress."

### C2: "I can't justify $350/yr just for tracking"

They've found the paid solutions and bounced on price. Small WooCommerce stores doing $5–50k/yr where PixelYourSite Pro at $359 is a hard sell.

**Where they are:** WordPress.org plugin search, budget-focused ecommerce communities, searching "pixelyoursite free alternative."

### C3: "I tried GTM server-side and it's too complex"

They've actively attempted a solution and failed or given up. GTM server containers, cloud hosting, custom subdomains — it was too much.

**Where they are:** Support threads for Conversios/Stape where people complain about setup. WordPress forums.

### C4: "I'm using Meta for WooCommerce but it's terrible"

They installed Meta's official plugin because it was free and official. Then it broke their site. They want something better.

**Where they are:** WordPress.org support forum for Meta for WooCommerce. Facebook groups asking "why does Meta for WooCommerce keep breaking?"

---

## How the Two Pillars Work Together

The pillars run in parallel. Universal creates awareness, Competitive differentiates.

**Universal:** "I didn't know my ad data was wrong" → now they know they need server-side tracking.

**Competitive:** "I already have tracking but it's not working / too expensive / too complex" → now they know UniPixel is the better option.

Universal content feeds Competitive content. Someone reads "Why your ad data is wrong" (Universal), understands the problem, then searches for a solution and finds "How to set up Meta CAPI for free on WooCommerce" (Competitive). The pipeline works because both pieces exist.

The wordpress.org readme must serve both pillars. Universal visitors need to quickly understand why they need this. Competitive visitors need to see the feature list, platforms supported, and how UniPixel compares.

---

## Competitive Landscape

### Direct Competitors

| Plugin | Installs | Rating | Price/yr | Server-Side | Notes |
|---|---|---|---|---|---|
| **PixelYourSite Pro** | ~500k | 4.3/5 | $359 | Yes | Market leader. Pinterest/Bing paid add-ons. UX criticised as cluttered. |
| **Pixel Manager** (SweetCode) | ~50k | 4.9/5 | $149–228 | Via their cloud | Highest rated. WooCommerce-only. Requires SweetCode Cloud (vendor lock-in). |
| **Conversios** | ~60k | 4.3/5 | $250–499 | Needs GTM | Bundles product feed management. Server-side requires GTM server container. |
| **Meta for WooCommerce** | ~500k | 2.2/5 | Free | Meta CAPI only | Official Meta plugin. 308 one-star reviews. Breaks frequently. Meta-only. |
| **Google Site Kit** | ~5M | 4.2/5 | Free | No | Dashboard/reporting tool, not a conversion tracker. No ecommerce events, no CAPI. |

### Other Notable Players

| Plugin | Model | Notes |
|---|---|---|
| **wetracked.io** | SaaS, $49–249/mo | WooCommerce + Shopify. Cloud-based, not a WP plugin. Priced for mid-to-large stores. |
| **Stape** | Plugin + hosting, ~$20/mo | Plugin is free, hosting is the product. Users pay for server container infrastructure that UniPixel eliminates entirely. Competitive angle: UniPixel does what Stape does — without the container, without the infrastructure overhead, without the GTM expertise. |
| **Conversion Bridge** | Integration-focused | 55 WP plugins connected to 16 analytics + 6 ad platforms. More "Zapier for tracking." |
| **TrackSharp** | WP-native server-side | GA4 only. No Meta, no TikTok, no Microsoft. Niche. |

---

## UniPixel vs Competitors — Feature Matrix

| Feature | UniPixel | PixelYourSite Pro | Pixel Manager Pro | Conversios Pro | Meta for Woo |
|---|---|---|---|---|---|
| **Meta Pixel + CAPI** | Yes | Yes (free!) | Pro only | Pro only | Yes |
| **GA4 + Measurement Protocol** | Yes | Pro | Pro | Pro | No |
| **TikTok + Events API** | Yes | Pro | Pro | Pro | No |
| **Microsoft UET** | Yes | Add-on ($) | Pro | Pro | No |
| **Pinterest** | Yes | Add-on ($) | Pro | Pro | No |
| **Snapchat** | No | No | Pro | Pro | No |
| **LinkedIn** | No | No | Pro | Pro | No |
| **Server-side (no GTM)** | Yes | Yes | Yes (via Cloud) | Needs GTM | Yes (Meta only) |
| **Self-hosted (no vendor)** | Yes | Yes | No (SweetCode Cloud) | No (GTM hosting) | Yes (Meta only) |
| **WooCommerce events** | Yes | Yes | Yes | Yes | Partial |
| **Custom click events** | Yes | Pro | Pro | Pro | No |
| **Non-WooCommerce sites** | Yes | Yes | No | No | No |
| **Consent management** | Built-in + 9 CMPs | Separate plugin ($) | Built-in | Built-in | No |
| **Event deduplication** | All platforms | Yes | Yes | Yes | Meta only |
| **Multi-pixel support** | No | Pro | Pro | ? | No |
| **GTM required** | No | No | No | Yes (for SST) | No |
| **Product feed sync** | No | No | No | Yes | Meta only |

---

## Why UniPixel Beats Each Competitor — Genuine Arguments

> **This is the real competitive case, competitor by competitor. Not feature ticks — actual reasons someone would choose UniPixel over each alternative. These must be honest and hold up under scrutiny.**

### vs PixelYourSite Pro (~500k installs, $359/yr)

**Their strength:** Market leader. Largest install base. Covers Meta in the free version. Established trust.

**Where UniPixel wins:**
- All 5 platforms included — PixelYourSite charges extra for Pinterest and Microsoft as paid add-ons on top of the $359/yr Pro price
- Custom click events included — PixelYourSite paywalls these behind Pro
- Consent management built in — PixelYourSite sells ConsentMagic as a separate paid plugin
- Simpler, cleaner interface — PixelYourSite's UX is widely criticised as cluttered and overwhelming
- Self-hosted server-side with no external infrastructure — same as PYS here, but without the add-on pricing model

**Where they win:** Install base, brand recognition, track record. Multi-pixel support (Pro).

**The pitch:** Everything PixelYourSite charges add-ons for, UniPixel includes. Same self-hosted approach, less complexity, no upsell treadmill.

---

### vs Pixel Manager / SweetCode (~50k installs, $149–228/yr)

**Their strength:** Highest rated plugin in the space (4.9/5). Clean interface. Good documentation.

**Where UniPixel wins:**
- Self-hosted — Pixel Manager routes server-side data through SweetCode Cloud (their own servers). Your tracking depends on their infrastructure staying up and their pricing staying the same
- Works on non-WooCommerce sites — Pixel Manager is WooCommerce-only. UniPixel handles custom events on any WordPress site (lead gen, content, SaaS)
- No vendor lock-in — if SweetCode raises prices, changes terms, or shuts down, Pixel Manager users lose server-side tracking. UniPixel runs on your server

**Where they win:** Polish, documentation, rating. Their cloud approach is simpler for users who don't care about self-hosting.

**The pitch:** Pixel Manager sends your data through someone else's servers. UniPixel sends it from yours. And it works on any WordPress site, not just WooCommerce.

---

### vs Conversios (~60k installs, $250–499/yr)

**Their strength:** Bundles product feed management. GA4 integration is strong. Good for stores that need feeds + tracking in one plugin.

**Where UniPixel wins:**
- No GTM server container required — Conversios requires a GTM server container for server-side tracking, meaning separate cloud hosting ($100–150/mo), 15–20 hours of technical setup, and ongoing maintenance
- Works on non-WooCommerce sites — Conversios is WooCommerce-focused
- Simpler setup — no GTM expertise needed, no container configuration, no cloud provisioning
- No invisible infrastructure costs — Conversios' server-side comes with ongoing hosting bills that grow with traffic

**Where they win:** Product feed management (not UniPixel's lane). Established in the Google/GA4 space.

**The pitch:** Conversios makes you build and maintain server infrastructure just to send conversion data. UniPixel uses the server you already have.

---

### vs Meta for WooCommerce (~500k installs, free)

**Their strength:** Official Meta plugin. Free. First thing people find when Meta tells them to set up CAPI.

**Where UniPixel wins:**
- Actually works — Meta for WooCommerce is rated 2.2/5 with 308 one-star reviews. It breaks sites, conflicts with other plugins, and has chronic reliability issues
- 5 platforms, not 1 — Meta's plugin only covers Meta. UniPixel covers Meta, Pinterest, TikTok, Google, and Microsoft
- Custom events — Meta's plugin doesn't support custom click/interaction events
- Consent management — Meta's plugin has no consent handling
- Doesn't break sites — this is the most common complaint in Meta for WooCommerce reviews

**Where they win:** It's the official Meta plugin. Some users trust "official" regardless of quality.

**The pitch:** Meta's plugin is the obvious first choice. It's also rated 2.2 stars for a reason. UniPixel covers Meta and four other platforms without breaking your site.

---

### vs Stape (plugin free, hosting ~$20/mo)

**Their strength:** Focused product. Good GTM server container hosting. Clear pricing tiers.

**Where UniPixel wins:**
- No server container needed — Stape's entire model is hosting GTM server containers. UniPixel eliminates the container entirely
- No GTM expertise required — Stape assumes you know how to configure GTM server-side tags, triggers, and variables. UniPixel handles it inside WordPress
- No ongoing infrastructure to manage — Stape's hosting is another moving part that can break, scale unexpectedly, or change pricing. UniPixel runs on what you already have
- No infrastructure dependency — if Stape has an outage or you stop paying, your tracking stops. UniPixel runs on your server

**Where they win:** For users already deep in the GTM ecosystem, Stape integrates with their existing setup. Stape also supports non-WordPress platforms.

**The pitch:** Stape sells hosting for infrastructure UniPixel doesn't need. Without the expense of maintaining server containers, without the complex hosting setups required.

---

### vs wetracked.io ($49–249/mo)

**Their strength:** Purpose-built for ecommerce attribution. Shopify + WooCommerce. Advanced attribution modelling.

**Where UniPixel wins:**
- WordPress-native — wetracked.io is a SaaS, not a plugin. Your data goes to their cloud
- No monthly SaaS bill — wetracked.io starts at $49/mo ($588/yr) and scales to $249/mo ($2,988/yr)
- Self-hosted — no dependency on their platform staying up or their pricing staying stable
- Works on any WordPress site — wetracked.io is ecommerce-focused

**Where they win:** Advanced attribution modelling, cross-platform reporting, Shopify support. Different product really — more analytics platform than tracking plugin.

**The pitch:** wetracked.io is an analytics SaaS. UniPixel is a WordPress plugin that sends your data directly from your server. Different tools for different needs — but if all you need is accurate server-side tracking, UniPixel does it without the SaaS bill.

---

## UniPixel's Genuine Differentiators

### 1. Self-hosted server-side tracking — zero external dependencies

Every competitor that does server-side either requires GTM server containers (Conversios), routes through their cloud (Pixel Manager's SweetCode Cloud), or needs separate hosting (Stape). UniPixel fires directly from the WordPress server to platform APIs. No middleman, no vendor lock-in, no extra hosting cost.

### 2. All 5 platforms at one price, no add-on upsells

PixelYourSite charges extra for Pinterest and Bing. Pixel Manager paywalls most platforms behind Pro. UniPixel ships Meta + Pinterest + TikTok + Google + Microsoft in the base product.

### 3. Works on non-WooCommerce sites

Pixel Manager is WooCommerce-only. Conversios is WooCommerce-focused. UniPixel handles both WooCommerce events and custom click events on any WordPress site — lead gen, SaaS, content sites.

### 4. Built-in consent management reading 9 CMPs

Reads consent state from OneTrust, Cookiebot, Osano, Silktide, Orest Bida, Complianz, CookieYes, Moove GDPR, or its own popup. Most competitors lack this, sell it separately (PixelYourSite's ConsentMagic), or support fewer CMPs.

### 5. Lightweight — no build step, no external JS bundles

Pure PHP + vanilla JS. No webpack, no cloud infrastructure, no code splitting.

---

## GTM Positioning — Clarity Note

> **This is not a front-facing differentiator. It's a secondary benefit that belongs toward the back of any feature list (~8th out of 12). Writing it down here to prevent it being overstated or misstated in future content.**

**What UniPixel actually does with respect to GTM:** UniPixel allows users to manage custom event tracking (button clicks, form submissions, interactions) inside WordPress — removing the need to use Google Tag Manager for that specific task. For users whose only reason for GTM was custom event management, UniPixel can replace it entirely.

**What UniPixel does NOT do:** It does not replace GTM for script inclusion, tag sequencing, or the many other things GTM handles. GTM remains a valid and common tool in the wider ecosystem. UniPixel works alongside GTM — it cooperates, it does not compete.

**How to communicate this (when it comes up):**
- Headline: "Manage custom events without needing Google Tag Manager"
- Extended: "UniPixel lets you set up and manage custom event tracking inside WordPress — so in some cases you can reduce or remove your dependency on Google Tag Manager for event management."
- Never say "No GTM" as a standalone claim. It's misleading and meaningless to most people.
- Never position GTM as the enemy. Many UniPixel users will also use GTM. They coexist.

---

## UniPixel's Gaps (Honest)

| Gap | Impact | Priority |
|---|---|---|
| **No Snapchat, LinkedIn, X, Reddit** | Matters to agencies. Not a blocker for solo store owners. | Medium — Pro tier candidates |
| **One pixel ID per platform** | Edge case. Mostly agencies. | Low |
| **No first-party proxy for ad blocker resilience** | Server-side calls survive ad blockers. Client-side don't. | Medium |
| **No product feed management** | Conversios bundles this. Not UniPixel's lane. | Low |
| **~100 installs, 6 reviews** | Affects search ranking, trust, and conversion. Growing. | Critical — actively improving |

---

## Feature Priority Framework

> **This is the decision process for evaluating what to build next. Apply it every time a feature is proposed or discussed.**

### The principle: effort-to-impact ratio wins

A feature that takes 2 hours and prevents a user from bouncing on the settings page is worth more than a feature that takes 2 weeks and impresses users who are already committed. Prioritise by the ratio, not by impact alone.

### Evaluation criteria (in order)

1. **Does it prevent abandonment?** — If a user installs UniPixel, opens the settings, and doesn't see something they expect (their CMP, their platform, a basic capability), they deactivate. Features that close these gaps come first regardless of how "small" they seem.

2. **What's the effort?** — A feature that follows an existing pattern (e.g. adding a CMP parser when 6 already exist) is near-zero risk and near-zero design cost. Prioritise pattern-following work over novel architecture.

3. **Does it protect the install base or grow it?** — Retention features (meeting expectations, preventing "this doesn't work for me" moments) are as valuable as acquisition features. A lost install is harder to win back than a new one is to gain.

4. **Is it a quick win?** — If the effort is under a day and the feature removes a real friction point, do it. Don't defer small things behind larger "strategic" features. Quick wins compound.

### Anti-patterns to avoid

- **"The system is solid so this can wait"** — Solid for existing users ≠ solid for the next user who has a different CMP / workflow / expectation.
- **"Low audience likelihood"** — Valid for deprioritising, but only when the effort is also high. If the effort is trivial, low audience likelihood doesn't justify skipping it.
- **"We should do the big thing first"** — Only if the big thing is blocking the small things. Otherwise, ship the small things while planning the big ones.

### Note: v2.5.1 optional server-side as onboarding improvement

Making server-side tracking optional (v2.5.1) removes the credential wall from initial setup. New users can install, paste a Pixel ID, and start tracking immediately — no access token required. This is good for onboarding because the biggest drop-off is during setup, and requiring API credentials upfront was one more reason to quit. Not a marketing angle — just an internal note that this feature quietly helps retention.

---

## Prioritised Feature Backlog

> **Living list. Ordered by effort-to-impact ratio. Update as features ship or priorities change.**

### Tier 1: Quick wins — high impact, low effort

| # | Feature | Effort | Why it matters | Status |
|---|---|---|---|---|
| 1 | **Add Complianz CMP parser** | ~1 hour | Popular WordPress-native CMP. User opens consent settings, doesn't see it, assumes UniPixel doesn't support their setup. Abandonment risk. Parser pattern already exists × 6. | Done |
| 2 | **Add CookieYes CMP parser** | ~1 hour | Large free tier, widely used. Same abandonment risk. Same implementation pattern. | Done |
| 3 | **Add Moove GDPR CMP parser** | ~1 hour | Common in WooCommerce specifically — the exact audience UniPixel targets. Same pattern. | Done |
| 4 | **Simplify consent dropdown** | ~2 hours | Reduced 9-vendor dropdown to 2 clear options (UniPixel banner / use existing CMP). Removes confusion, adds contextual helper text. Parsers already run regardless of selection. | Done |

### Tier 2: Medium effort, high impact — needs assessment

| # | Feature | Effort | Why it matters | Status |
|---|---|---|---|---|
| 5 | **Setup wizard / onboarding flow** | Days | Most deactivations happen in the first 10 minutes. A guided first-run experience (connect your first platform, verify it works) reduces this. Comes first — users must connect a platform before they'd ever hit custom events. | Needs assessment |
| 6 | **Custom events wizard** | Days | Current custom events require users to type CSS selectors (#id, .class) — unintuitive for non-developers. A guided wizard with visual element picking, example templates, and step-by-step flow would lower the barrier. Silent failure if selector is wrong. No test/preview. No validation. **Stopgap:** docs article explaining custom events setup (written, ready to publish). | Needs assessment |
| 7 | **Event diagnostics dashboard** | Days | Users can't tell if the plugin is working. A health screen showing last event sent, success/failure counts, connection status per platform. Data already exists in `unipixel_event_log`. | Not started |

### Tier 3: Known gaps, lower urgency

| # | Feature | Effort | Why it matters | Status |
|---|---|---|---|---|
| 8 | **Microsoft WooCommerce pipeline** | Days | Only platform without server-first WooCommerce events. Low audience share for Bing Ads, but a visible gap. | **Done (v2.6.0)** — code complete, but Microsoft CAPI token access is not yet self-service. Server-side untested against live endpoint. Client-side UET confirmed working. Do not advertise CAPI until token access is available and server-side is verified. |
| 9 | **Advanced Matching (billing address + AM fields)** | Hours | Sends hashed PII with events to improve platform match quality. | Done (v2.5.3) |
| 10 | **external_id population** | Hours | Never populated. Needs strategy decision on what to use as identifier. | Not started |
| 11 | **Additional platforms (Snapchat, LinkedIn)** | Weeks | Pro tier candidates. Matters to agencies. Not a blocker for solo store owners. | Not started |

---

## Messaging Voice

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

**Core copywriting principles:**

1. Lead with the problem, not the feature.
2. Name the cost of not acting — but don't oversell. It's the better move, not the fix.
3. Be specific — "Meta Conversion API, GA4 Measurement Protocol, TikTok Events API" not "all major platforms."
4. For Competitive content: differentiate on simplicity, self-hosting, and reliability — not on price.
5. For Universal content: educate without selling — let the reader arrive at "I need this" on their own.
6. Never absolute. Always comparative. "More accurate" not "complete." "Better" not "perfect."
7. Never lie about install counts or social proof.
8. Don't promise correlation between WooCommerce and platform numbers. It will never be solved.
9. Don't use platform warnings (Meta match quality scores etc.) as pain points. That's doing Meta's marketing.
10. Don't include setup instructions in marketing copy. Communicate the outcome and the simplicity — not the steps.

---

## Pricing Strategy — Lock-In Gate Model

### The model

Every user gets the full product. No feature gates, no crippled free tier, no "upgrade to unlock platforms." All 5 platforms, server-side tracking, Advanced Matching, consent management, custom events — everything works from day one.

Once the user is embedded — platforms configured, credentials entered, data flowing, events deduplicating — a paywall activates. The plugin continues working, but requires a paid licence to stay active past the gate.

**Why this works:** The switching cost does the selling. A user who has configured Meta, TikTok, Pinterest, Google, and Microsoft with access tokens, pixel IDs, ad account IDs, custom events, and consent rules is not going to tear all that out and start over with a competitor. The value is proven by the time the gate hits — they've seen their data improve, their event match rates go up, their campaigns optimise better. The $89/yr ask is trivial against that.

**Why not feature-gated freemium:** Feature gates create a worse product experience during the evaluation window — exactly when you need to prove value. If a user can't try server-side tracking until they pay, they never feel the difference. The lock-in gate lets them feel the full value, then asks them to keep it.

### Trigger mechanism — options under consideration

| Trigger | How it works | Pros | Cons |
|---|---|---|---|
| **Time-based** | Full access for N days (e.g. 14 or 30), then paywall | Simple to implement, easy for users to understand, standard trial model | Users can reinstall/reset, time pressure may rush evaluation |
| **Event-count** | Full access until N server-side events sent (e.g. 1,000 or 5,000), then paywall | Directly tied to value delivered, scales with store size, harder to game | Requires tracking infrastructure, small stores may never hit threshold, number feels arbitrary |
| **Hybrid** | Whichever comes first — time OR event count | Covers both edge cases | More complex to communicate |

**Key considerations:**
- **Measurability** — whatever the trigger, it needs to be trackable without adding heavy infrastructure. The plugin already logs events to `unipixel_event_log` and maintains `unipixel_log_count`, so event-count tracking is partially built.
- **Anti-circumvention** — the gate check should be simple but not trivially bypassable. A timestamp stored in `wp_options` can be reset by deactivating/reactivating. Event counts in the DB can be zeroed. The mechanism needs to be resilient without being obnoxious — this is a WordPress plugin, not a DRM system. Server-side licence validation (ping a licensing server) is the standard WordPress approach and prevents local tampering.
- **Transparency** — the user should know from the start that this is a trial, not a permanent free product. Upfront messaging: "Full access for [trigger]. Then $89/yr to keep it running." No bait-and-switch. The WordPress.org listing and onboarding flow should make this clear.
- **Grandfathering** — early adopters who installed during the growth phase (pre-paywall) should be handled thoughtfully. Options: lifetime free, extended trial, discounted first year, introductory pricing. These users helped validate the product and build the install base.

### Price point

**$89/yr.** This is the floor. Sits at the "impulse buy for a business" threshold — a store owner spending money on ads won't blink at $89/yr for better conversion data. Competitors charge $359/yr (PixelYourSite Pro) and $499/yr (Conversios). Even at $89, UniPixel is positioned well below them while delivering more platform coverage.

Room to go higher ($99–129/yr) once retention data proves users stick. The first cohort of paying users will reveal willingness-to-pay.

### Revenue modelling

| Active Installs | Conversion Rate | Paying Users | At $89/yr |
|---|---|---|---|
| 10,000 | 2% | 200 | $17,800/yr |
| 10,000 | 5% | 500 | $44,500/yr |
| 10,000 | 10% | 1,000 | $89,000/yr |
| 30,000 | 3% | 900 | $80,100/yr |

**Freemium conversion benchmarks:** Industry average for WordPress freemium: 1–3%. The lock-in gate model should outperform traditional freemium because users have already experienced the full product and built switching costs by the time they're asked to pay. Target: 5–10%.

### Introductory offers — options

- **Early-bird annual:** $59/yr for first year, $89/yr after — rewards early adopters, creates urgency
- **Lifetime deal:** One-time $199–249 — appeals to deal-seekers, generates upfront cash, but caps long-term revenue
- **First-year discount:** 30–50% off first year for users who convert within the trial period

These are tools to deploy once the gate is live, not decisions needed now.

### What needs to be decided before implementation

1. **Trigger type** — time-based, event-count, or hybrid
2. **Trigger threshold** — how many days / how many events
3. **Licence validation approach** — local-only vs server-side ping
4. **Grandfathering policy** for pre-paywall installs
5. **WordPress.org listing language** — needs to be transparent about the trial model to comply with plugin directory guidelines
6. **Onboarding messaging** — when and how to communicate the trial terms

---

## Growth Strategy — Path to 10,000 Installs

**Goal:** 10,000 active installs. Then introduce Pro tier at $89/yr.

### Growth Channels — Real Actions

> **The product is competitive. The problem is nobody knows it exists. Everything below is about getting found. Ordered by what can actually move the needle at ~100 installs.**

---

#### Channel 1: YouTube Videos

**Why this matters:** Very little video content exists for WordPress-native server-side tracking without GTM. The niche is underserved. Video builds trust faster than any other medium — people see the admin UI, see how simple setup is, and install. YouTube has long-tail discovery — a good video ranks for years.

**What to make:**
- "How to set up Meta Conversions API on WooCommerce" — the money video. 5–10 minutes, screen recording, showing actual setup from install to first event firing
- "How to set up TikTok Events API on WooCommerce" — same format
- "How to set up Pinterest Conversions API on WooCommerce" — same format
- "What is server-side tracking and why your WooCommerce store needs it" — Universal, educational
- "Track custom events on WordPress without Google Tag Manager" — demonstrates the custom events UI

**Format:** Screen recordings with voiceover. No production budget needed. Authentic > polished. Show the real product.

**Status:** Not started. High priority.

---

#### Channel 2: Documentation & Support Articles on buildio.dev

**Why this matters:** Not blog posts — practical, doc-style support content. These serve double duty: help existing users AND capture search traffic. When someone searches "how to set up pinterest conversions api wordpress", a well-written doc article on buildio.dev can rank. Already proven: setup docs for TikTok and Pinterest have been written in this format.

**What to publish:**
- Platform setup guides (Meta, Pinterest, TikTok, Google, Microsoft) — some already done
- Custom events setup guide (written, ready to publish)
- Advanced Matching explanation
- Consent management setup
- Troubleshooting / FAQ content
- Competitor comparison pages (UniPixel vs PixelYourSite, UniPixel vs Stape, UniPixel vs Meta for WooCommerce)

**Format:** Practical, step-oriented, SEO-targeted. Not marketing fluff — genuinely helpful content that happens to rank.

**Status:** Partially done. Ongoing.

---

#### Channel 3: Community & Forum Presence

**Why this matters:** At ~100 installs, the fastest path to the next 500. Manual and slow per-user, but these are high-intent people with active problems UniPixel solves. Being genuinely helpful builds trust and creates word-of-mouth.

**Where to be:**

| Community | Why | What to look for |
|---|---|---|
| **WordPress.org — Meta for WooCommerce support forum** | 308 one-star reviews. Thousands of frustrated users actively looking for alternatives. | "Plugin broke my site", "not tracking conversions", "conflicts with X" |
| **Reddit — r/woocommerce** | Store owners asking about tracking setup | "meta capi", "conversion tracking", "server side" |
| **Reddit — r/PPC, r/FacebookAds** | Advertisers who understand the data problem | "pixel not tracking", "capi setup", "conversions not matching" |
| **Reddit — r/wordpress** | General WordPress users discovering tracking needs | "tracking plugin", "analytics", "meta pixel" |
| **Facebook groups — WooCommerce, WordPress, Meta Ads** | Large audiences, frequent questions about tracking | Same search terms as Reddit |
| **WordPress.org — support forums for Conversios, PixelYourSite** | Users struggling with competitor complexity | "too complicated", "GTM setup", "not working" |

**Approach:** Answer the question first. Be helpful. Mention UniPixel when it's the genuine answer to their problem — not before.

**Status:** Not started. Needs to become a regular habit, not a one-off.

---

#### Channel 4: Platform Partnerships

**Why this matters:** Pinterest's own ads manager setup documentation recommends PixelYourSite as the plugin to use. That's a direct pipeline of every Pinterest advertiser using WordPress being sent to a competitor. If UniPixel could get listed as a recommended integration by any platform, that's instant credibility and a stream of high-intent users.

**Targets:**
- **Pinterest** — UniPixel now has full Pinterest Conversions API support. A legitimate case exists to be listed alongside PixelYourSite in their integration docs
- **Meta** — Meta has a technology partners / integration directory for Conversions API implementations
- **TikTok** — TikTok has a Marketing Partners program for Events API integrations
- **Google** — GA4 Measurement Protocol integrations may have a directory or certification

**What's needed:** Each platform has different requirements. Likely involves: documenting the integration formally, meeting technical requirements, applying through their partner program, and possibly demonstrating install base / usage.

**Reality check:** Some programs have minimum install thresholds. Pinterest may be the most accessible since the integration is fresh and complete. Worth investigating requirements for each.

**Status:** Not started. High potential impact. Needs research into each platform's partner program requirements.

---

#### Channel 5: Paid Advertising

**Why this matters:** Necessary evil at this stage, but the role of paid ads evolves as the business grows. It's not one thing — it's three phases.

**Real data point:** ~1,000 clicks for ~$30 is achievable. That's $0.03/click.

| Click-to-install rate | Installs per $30 | Cost per install |
|---|---|---|
| 2% | 20 | $1.50 |
| 3% | 30 | $1.00 |
| 5% | 50 | $0.60 |

**Phase 1: Bootstrap (now → ~500 installs)**
Buy enough installs to cross the threshold where WordPress.org organic discovery starts working and the listing looks credible. This is a one-time investment, not a growth strategy. Estimated cost: $300–500 to go from 100 to 500 installs. Not sustainable, not meant to be.

**Phase 2: Validated acquisition (500+ installs, retention proven)**
Once installs prove that people download it AND keep it installed, ads become a validated acquisition channel. The product retains — now spending to grow is justified. The key metric between Phase 1 and Phase 2 is retention: do people keep it installed after a week? After a month? Downloads alone mean nothing if they deactivate. This is the validation signal.

**Phase 3: Revenue channel (post-Pro launch)**
Once Pro tier is live, ads become a revenue channel with real unit economics. At $1/install and 3% Pro conversion at $89/yr, that's ~$33 CAC for an $89/yr customer. That's a real business with positive ROI on ad spend. At this point, scaling ads is scaling revenue.

**Ad channels:**
- **Google Ads** — targeting competitive search queries. Highest intent. People searching "meta capi plugin woocommerce" are ready to install something.
- **Meta Ads** — Universal messaging to WooCommerce store owners. Broader reach, lower intent, needs strong creative.
- **YouTube Ads** — pre-roll on competitor tutorials. Only viable once UniPixel's own videos exist.

**Status:** Not started. Phase 1 can begin anytime — the budget is small and the goal is clear.

---

#### Channel 6: Third-Party Advocates

**Why this matters:** Other people recommending UniPixel is more credible than UniPixel recommending itself. One YouTube review from a WooCommerce creator with 10k subscribers can drive more installs than months of forum posting.

**Targets:**
- **WordPress/WooCommerce YouTube reviewers** — creators who do plugin comparisons and tutorials. Reach out, offer the plugin for review. No payment needed if the product is genuinely good — reviewers want good content.
- **WooCommerce agencies** — agencies that build stores for clients need tracking solutions. If UniPixel is their go-to recommendation, every client is an install.
- **Plugin comparison bloggers** — sites that write "best WooCommerce tracking plugins" listicles. Getting included in these is high-value SEO.
- **WordPress newsletter curators** — WP Weekly, MasterWP, Post Status. Getting featured as a noteworthy plugin.

**When affiliates make sense:** Once Pro tier launches, an affiliate program (20–30% commission) gives advocates a financial reason to recommend UniPixel. Before that, the pitch is purely "this is a good product your audience will benefit from."

**Status:** Not started. Needs outreach list and approach.

---

### Channel Priority — What to Do First

| Priority | Channel | Why first |
|---|---|---|
| 1 | **Community & forums** | Fastest path to next 100–500 installs. Can start today. Zero cost. |
| 2 | **YouTube videos** | Underserved niche. Long-tail discovery. Trust builder. High effort but high payoff. |
| 3 | **Docs on buildio.dev** | Partially done. Captures search traffic. Supports all other channels (forums link to docs, videos link to docs). |
| 4 | **Platform partnerships** | Research phase — find out what's required. Pinterest first (integration is fresh and complete). |
| 5 | **Third-party advocates** | Start identifying targets. Outreach once videos and docs exist to point them to. |
| 6 | **Paid ads** | Last. Needs social proof to convert. Plan the targeting now, spend later. |

---

### WordPress.org Optimisation (Ongoing)

Readme rewrite (known issues: short desc truncated, WooCommerce missing from title, only 4/12 tags, typos), more screenshots, support forum responsiveness, regular updates every 4–8 weeks to avoid the 180-day ranking penalty. This is table stakes — it's the landing page everything else points to.

---

## Content Plan

### Universal Content (Educate / Create Demand)

| Content Piece | Format | Target Audience |
|---|---|---|
| Why Your Ad Data Is Wrong | Blog post | Store owners who don't know they have a problem |
| What Is Server-Side Tracking and Why It Matters | Blog + video | People who've heard the term but don't understand it |
| The Ad Blocker Problem: How Your Data Disappears | Blog / social | Broad awareness — shareable, stat-driven |
| Meta Keeps Telling You to Set Up CAPI — Here's What That Means | Blog + video | People who've seen Meta warnings but don't know what to do |

### Competitive Content (Convert Intent)

| Content Piece | Format | Target Audience |
|---|---|---|
| How to Set Up Meta CAPI on WooCommerce — No Server Container Needed | Blog + video | People actively searching for the fix |
| Server-Side Tracking Without Paying for Server Containers | Blog post | People who've seen the infrastructure complexity and bounced |
| UniPixel vs Stape — Simpler, No Container | Blog post | People evaluating Stape or already managing GTM server hosting |
| PixelYourSite vs UniPixel | Blog post | Comparison shoppers evaluating alternatives |
| Alternatives to PixelYourSite Pro | Blog post | People searching for PixelYourSite alternatives |
| Manage Custom Events Without Google Tag Manager | Blog post | People who find GTM too complex for event management |
| UniPixel vs Meta for WooCommerce | Blog post | People frustrated with Meta's plugin |

---

## WordPress.org Readme — Display Rules

### Character limits and search weight

| Field | Limit | Search Weight | Where It Appears |
|---|---|---|---|
| **Title** | No hard limit (keep reasonable) | Highest | Search results heading, plugin page h1 |
| **Short description** | **150 characters max** | High (2nd most important) | Search result cards, wp-admin Add Plugins screen |
| **Tags** | 5 displayed by directory, up to 12 total | Medium | Directory search index (first 5), Google indexing (6–12) |
| **Full description** | ~1,500 words before truncation | Medium | Plugin page main content |

### Search ranking factors (in order)

| Factor | Weight |
|---|---|
| Title keywords | Highest |
| Short description keywords | High |
| Ratings/reviews (0 reviews = treated as 2.5 stars) | Very high |
| Active install count | High |
| Full description + tags | Medium |
| Support ticket resolution rate | Medium |
| Last update recency (180+ days = penalty) | Medium |

### Tag update rules (sticky behaviour)

- Tags read from `/tags/{version}/readme.txt` in SVN, not trunk
- Editing trunk readme.txt does NOT update tags if `Stable tag` points to a version number
- To update tags: edit readme.txt inside the current stable tag folder, or release a new version
- Cache: ~10 minutes for search, up to 6 hours for page display

### Current readme.txt issues

| Issue | Detail |
|---|---|
| Short description over limit | 165 chars (limit: 150). Gets silently truncated mid-word. |
| Typo in short description | "...Google using." — orphaned word from an edit |
| Microsoft missing | Not in title, not in short description, barely in body |
| WooCommerce missing | Not in title — a huge search term gap |
| Only 4 tags used | 5th slot empty. Tags 6–12 (Google-indexed) completely unused. |
| Tags too specific | "Facebook Conversion API" — nobody searches that exact phrase on WP.org |
| Setup section too long | Credential instructions take 60% of the readme. Should be docs or FAQ. |
| Multiple typos in body | "additioal", "mintues", "inWordPress", "debgugging", "vents", "inlcuding" |
| Only 3 screenshots | More screenshots = more visual trust |

### Readme text options under consideration

**Title options:**

A. `UniPixel: Meta Conversion API, TikTok & Google Server-Side Tracking for WooCommerce` (82 chars)

B. `UniPixel: Free Server-Side Conversion Tracking for Meta, Google, TikTok & Microsoft` (80 chars)

C. `UniPixel: Server-Side Conversion API Tracking for Meta & Google` (65 chars)

**Short description options (150 char max):**

A. `Server-side tracking for Meta CAPI, GA4 and TikTok from your own WordPress server. No external cloud, no GTM setup. WooCommerce and custom events.` (148 chars)

B. `Meta Conversion API, GA4 Measurement Protocol and TikTok Events API built in. Server-side and client-side. No cloud hosting or GTM required.` (146 chars)

C. `Connect Meta, Google, TikTok and Microsoft with server-side and client-side event tracking. WooCommerce events, custom events and consent included.` (149 chars)

D. `Send conversion events from your WordPress server to Meta, Google, TikTok and Microsoft. No extra cloud, no GTM. Includes WooCommerce tracking.` (143 chars)

**Tag options:**

A. `meta conversion api, server-side tracking, tiktok pixel, facebook capi, woocommerce pixel`

B. `meta conversion api, facebook server side, tiktok events api, conversion tracking, google measurement protocol`

C. Keep current + fill 5th slot: `Meta Pixel, Facebook Conversion API, TikTok Events API, Server-side Tracking, meta capi`

---

## Market Trends

1. **Server-side tracking is now table stakes**, not a premium feature. The drivers: Safari ITP, Chrome privacy sandbox, Firefox ETP, iOS ATT, ad blockers (~43% adoption). Having CAPI is baseline. The differentiator is how simply you deliver it.

2. **Google Consent Mode v2** is mandatory for EU traffic since March 2024. Server-side helps fill modelling gaps.

3. **The pain is complexity, not missing platforms.** Users don't want 14 platforms — they want Meta + Google to work properly without a $350 plugin or a GTM consultant.

4. **"No GTM" is becoming a selling point.** GTM server-side setup is 15–20 hours and $100–150/month ongoing. WordPress-native alternatives are in demand.

---

## Positioning — The Core Message

**"Server-side tracking that actually works, with zero setup complexity."**

The wedge into the market is not more platforms or more features. It's being the answer to: "Meta says set up Conversions API — how do I do that simply?"

---

## Pricing Landscape

| Plugin | 1-site annual |
|---|---|
| Conversios Pro | $499 (often "50% off" = ~$250) |
| PixelYourSite Pro | $359 |
| Pixel Manager Pro | $149–228 |
| **Market sweet spot** | **$79–149** |
| UniPixel (current) | Free |

---

## What "Done" Looks Like at 10,000 Installs

- 10,000 active installs on wordpress.org
- 20–30 genuine reviews, 4.5+ star average
- 3–5 blog posts ranking on page 1 for target search queries
- YouTube channel with 5–10 tutorial videos, 10k+ total views
- Established presence in 3–4 WooCommerce/WordPress communities
- Pro tier live at $89/yr with clear free vs Pro comparison
- 200–500 paying customers (2–5% conversion) = $18k–$44k/yr revenue


## Ad examples

Ad 1: Your ad data is wrong.
Context: Browsers are blocking your results before they reach your ad platforms.
Rainbow: UniPixel sends it directly your WordPress website. Install and it's handled. Free.

Ad 2: Right data
Bang: Are you tracking what matters?
Context: Platforms don't want your page views. They want the events you care about.
Rainbow: UniPixel tracks and sends them server-side from your WordPress website. No GTM. Free.

Ad 3: Custom events
Bang: Track any interaction on your site.
Context: Buttons, forms, popups — if it happens on your site, you should be measuring it.
Rainbow: UniPixel lets you set up custom events and send them server-side. No code. No GTM.