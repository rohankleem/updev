# Campaigns, Channels & Growth Operations

High-cadence file. Active ad campaigns, owned channels, growth plan, content plan, wp.org readme operations. The operational playbook.

For foundational positioning (pillars, industry problem, language rules, competitor analysis) see `positioning.md`. For what product work is prioritised see `priorities.md`.

---

## Active campaigns — March 2026

**Focus: PixelYourSite displacement.** All four campaigns target the same competitor ecosystem from different angles simultaneously. Duration: ongoing. Review and iterate based on results.

### Overview

| Campaign | Technique | Channel | Role |
|---|---|---|---|
| 1 | Perch and Poach | Forums, Reddit, FB groups | Credibility, brand seeding, Pinpoint hook collection |
| 2 | Pinpoint and Panorama (+ Pain/Rainbow) | Meta/Instagram Ads | Brand awareness + retargeting in PYS ecosystem |
| 3 | Perch and Poach | Google Search Ads | Competitor-name search interception (sniper) |
| 4 | Sweep and Strike / category | Google Search Ads | Category-search interception (main volume driver) |

All campaigns reinforce each other — forum presence creates brand recognition → Meta/Insta graphics create curiosity → Google campaigns catch downstream search traffic.

```
Forums (organic)     → "Who is UniPixel?"    → Google search → Campaign 4 captures them
Meta ad (graphic)    → "Interesting..."       → Google search → Campaign 4 captures them
Google C3 (competitor) → Landing page         → Install       → Convert
Google C4 (universal)  → Landing page         → Install       → Convert (main channel)
```

---

### Campaign 1 — Organic Forum Presence (Perch and Poach)

**Technique:** Perch and Poach
**Channel:** WordPress.org support forums, Reddit (r/woocommerce, r/PPC, r/FacebookAds), WooCommerce Facebook groups
**Cost:** Free — time only
**Status:** Active (3–5 helpful replies/week)

**What to do:**
- Find active threads where people struggle with PixelYourSite (CAPI setup, update breakages, consent conflicts, setup confusion, "is there an alternative?")
- Reply helpfully — answer the actual question, explain the concept, provide genuine value
- Mention UniPixel naturally at the end: *"If you want something that handles this automatically, UniPixel does it out of the box"*
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

### Campaign 2 — Meta/Instagram Ads (Pinpoint and Panorama Graphics)

**Technique:** Pinpoint and Panorama
**Channel:** Meta Ads (Facebook + Instagram feed/stories)
**Targeting:** People who have searched for, used, or engaged with PixelYourSite content
**Format:** 3 graphic creatives

**Audience targeting options:**
- Custom audience: website visitors who searched PixelYourSite-related terms
- Interest targeting: WooCommerce, Facebook Pixel, conversion tracking, digital advertising
- Lookalike from WordPress.org plugin page visitors (if pixel on unipixelhq.com)

**Creative approach:** Each graphic leads with a hyper-specific Pinpoint hook (real forum language) that makes the viewer think "that's exactly my problem." The graphic then opens to the Panorama — UniPixel as the solution to a whole world of tracking pain.

Graphics 1 and 2 exist (Pain/Rainbow format). Graphic 3 is the new Pinpoint and Panorama creative — to be designed in Figma. Run as A/B/C test.

### Pain / Rainbow problem areas (Campaign 2 source material)

**Problem Area 1 — Missing data:**

| | Pain | Rainbow |
|---|---|---|
| Hook | Your ad data is wrong. | See what's really happening. |
| Context | Your real results are being hidden from the platforms you pay for. | Data sent directly from your server — bypassing everything that blocks it. |
| Follow-up | You're making budget decisions based on numbers that are missing pieces. Ad blockers, iOS privacy and browser restrictions quietly stop your conversion data from reaching your ad platforms. | Your server knows every conversion. UniPixel sends that data straight to Meta, Google and TikTok. You get a more accurate picture — and you stop making decisions on incomplete numbers. |

> **Why "Your ad data is wrong" works:** Fear. Personal ("your"). About something they care about (ad performance, money). Doesn't mention tracking, pixels, server-side. Previous attempts ("Your tracking has blind spots", "You're getting more sales than your ads report") failed — first is jargon, second is good news.

**Problem Area 2 — Weaker ad performance:**

| | Pain | Rainbow |
|---|---|---|
| Hook | Your ads cost more than they should. | Spend less for the same results. |
| Context | Your platforms can't see your real results — so they waste your budget on the wrong people. | More accurate data means your platforms find better customers for less money. |
| Follow-up | Your ad platforms only see a fraction of your real results. They pull budget from what's working and shift it to what isn't. | When more of your results reach your ad platforms, the algorithm learns who actually converts. Cost per result comes down — because the data got more accurate. |

> **Separation rationale:** Area 1 is "your data is wrong." Area 2 is "that's why your ads cost too much." First is technical reality; second is the financial consequence. Both ended up in the same place early — fix was making the second about money and cost.

### Pinpoint and Panorama hooks (for Campaign 2 graphic 3)

| Pinpoint (3–8 words, real forum language) | Panorama (reframe + expand) |
|---|---|
| "Advanced Matching — what?" | Struggling to navigate the conversions world? UniPixel handles it. Advanced Matching, server-side tracking, event dedup — all set up, all automatic. |
| "Why does Meta say I have 12 purchases but WooCommerce says 47?" | Your pixel can only see what the browser lets through. UniPixel sends every conversion directly from your server — no browser in the middle. |
| "I set up CAPI and my events are doubling" | That's a deduplication problem. UniPixel matches event IDs automatically so platforms count each conversion once — not twice. |
| "My Event Match Quality score is stuck at 'Poor'" | Meta can't match your events to real people because the browser strips the data it needs. UniPixel sends user data server-side — match quality goes up without you touching anything. |
| *(source more from forums)* | |

**v2.6.6 Pinpoint hooks (Centralised Event Manager):**

| Pinpoint (3–8 words, real forum language) | Panorama (reframe + expand) |
|---|---|
| "How do I track a thank-you page?" | Thirty seconds in UniPixel: pick the page from a dropdown, pick "Lead", done. No GTM, no CSS, no copy-pasting the same event into five different platform settings pages. |
| "What's the Meta Lead event called in TikTok?" | TikTok calls it "Contact". Google calls it "generate_lead". Pinterest calls it "Lead". UniPixel fills in the right one for each platform automatically. You set up "Lead" once. |
| "Set up the same conversion 5 times in 5 places…" | UniPixel's Centralised Event Manager: one form, all five platforms, every standard event name handled. Edit once, propagates everywhere. |
| "Tracking lead-gen on WordPress without WooCommerce" | UniPixel works the same on non-WooCommerce sites. Lead, Newsletter Signup, Contact, Registration — pre-mapped to every platform's standard event name. Pick a thank-you page, pick a conversion type, save. |
| "Do I really need GTM just to fire Lead on /thank-you/?" | No. WordPress already runs PHP. UniPixel uses your existing server. Pick the page, pick the conversion, fires across Meta, Google, TikTok, Pinterest, Microsoft. |
| "PixelYourSite paywalls custom events" | UniPixel includes every custom event flow at one price — clicks, page-shown triggers, URL-match triggers, the new Centralised Event Manager. Nothing behind a Pro tier. |
| "Why does Google not let me have client AND server for Lead?" | Because for non-Purchase events Google permits one or the other. UniPixel enforces that rule for you in the Event Manager — one less thing to know, one less mis-configuration. |

**Source Pinpoints from:** WP.org support forums (Meta for WooCommerce, Conversios, PixelYourSite), Reddit (r/woocommerce, r/PPC, r/FacebookAds), Facebook groups.

---

### Campaign 3 — Google Ads Competitor Intercept (Perch and Poach) — LIVE

**Technique:** Perch and Poach
**Channel:** Google Search Ads (Search only — Display Network and Search Partners OFF)
**Targeting:** People actively searching for competitors by name
**Landing page:** `https://unipixelhq.com`
**Status:** Live since 14 March 2026. All 6 ad groups live (PYS, Stape, Conversios, PixelCat, Pixelavo, Meta Pixel WordPress). Initial serving blocked by advertiser verification (see Learnings); verification completed 6 April 2026.

#### Account & campaign settings

| Setting | Value |
|---|---|
| Google Ads account | rohankleem@gmail.com (267-541-9427) |
| Business name | Buildio |
| Billing entity | Elure Pty Ltd |
| Advertiser name | Buildio |
| Campaign type | Search (not Smart, not Performance Max) |
| Networks | Search only — Display Network OFF, Search Partners OFF |
| Bidding | Maximise clicks, max CPC A$0.35 |
| Daily budget | A$0.90 (reduced 16 March 2026 from A$6.00) |
| Locations | All countries and territories |
| Languages | English |
| AI Max | OFF |
| Ad rotation | Optimise: prefer best performing |

#### Keyword strategy (applies to all ad groups)

- **Exact match only** — `[brackets]`. Close variants always on; Google expands automatically.
- **One keyword per small brand** — for WP plugin competitors (Conversios, PixelCat, Pixelavo), bare brand `[brand]` is sufficient.
- **Modifiers for big brands** — Stape, PYS. Bare brand catches too much low-intent traffic (docs, login, support).
- **No bare brand keyword for big brands** — `[stape]` alone would show ads to existing users searching docs. Waste.
- **PYS exception** — currently has both bare and modifier keywords. Consider trimming bare if data shows low-intent clicks.

#### Expected performance

Weekly: ~8 clicks on PYS terms, ~A$0.32 avg CPC. Stape may add volume — watch. Sniper campaign — low volume, high intent. Campaign 4 drives volume.

#### Campaign-level sitelinks (shared across ad groups)

| Text | Description 1 | Description 2 |
|---|---|---|
| 5 Platforms One Plugin | Meta TikTok Google Pinterest Bing | All server-side tracking built in |
| Server-Side Built In | No extra servers or hosting | Tracks from your WordPress server |
| See How It Works | Install, configure, done | Setup takes 2 minutes |
| WooCommerce Ready | Purchase events fire automatically | Deduplication built in |

#### Ad groups

**PixelYourSite — All Intent** *(updated 16 March: trimmed to bare brand keywords — close variants cover modifiers automatically)*
- Keywords: `[pixelyoursite]`, `[pixel your site]`
- Headlines: "PixelYourSite Alternative", "Tired of PixelYourSite?", "$359/yr for Tracking? Really?", "5 Platforms — One Plugin", "PixelYourSite Keeps Breaking?", "15 Pages of Ignored Tickets", "CAPI Without the Headache", "UniPixel — Install and Go", "Events Doubling? We Fix That", "TikTok + Pinterest Included", "No Server Containers Needed", "Setup Takes 2 Minutes", "Your Tracking Shouldn't Break", "Dashboard Full of Upsells?", "Stop Paying $359/yr"
- Descriptions: "PixelYourSite charges $359/yr for TikTok and Pinterest. UniPixel includes all 5 platforms." / "No 40-step setup guides. No dashboard upsell nags. Just server-side tracking that works." / "Meta, Google, TikTok, Pinterest & Bing — CAPI built in, deduplication automatic." / "Tired of updates freezing your site? UniPixel is lightweight, reliable server-side tracking."

**Stape** — angle: WordPress users don't need Stape's infrastructure. Your server already does this.
- Keywords: `[stape alternative]`, `[stape alternatives]`, `[stape replacement]`, `[stape review]`, `[stape reviews]`, `[stape too expensive]`, `[stape expensive]`, `[stape pricing]`, `[stape cost]`, `[stape not working]`, `[stape problems]`, `[stape issues]`, `[stape complicated]`, `[stape support]`, `[stape wordpress]`, `[stape woocommerce]`, `[stape wordpress alternative]`, `[stape server side tracking]`, `[stape gtm server container]`, `[stape gtm hosting]`, `[stape server container]`, `[stape conversions api]`, `[stape capi]`
- Why modifiers only: Stape is cross-platform; bare `[stape]` would catch users looking for docs/login/support (wasted clicks). WordPress-specific ad copy is a secondary filter.
- Headlines anchor on "Your server already does this", "No containers needed", "No GTM. No Stape. Done.", "Stape Alternative for WordPress".

**Conversios, PixelCat, Pixelavo** — bare brand keyword only (small WP plugins, low volume, close variants cover alternatives/problems/etc).

**Meta Pixel WordPress** — phrase match (broader than exact for category catching):
- `"meta pixel wordpress"`, `"meta pixel plugin wordpress"`, `"meta pixel wordpress plugin"`, `"meta pixel code wordpress"`, `"meta pixel for wordpress"`, `"wordpress meta pixel"`
- Angle: Meta's own pixel is buggy, clunky, limited to one platform. UniPixel does Meta properly plus four more. Do NOT claim Meta lacks server-side (CAPI exists) — attack setup complexity, 2.7-star official plugin rating, single-platform limitation.

#### Ad group matrix

| | PYS | Stape | Conversios | PixelCat | Pixelavo |
|---|---|---|---|---|---|
| Type | WP plugin | Infra/hosting | WP plugin | WP plugin | WP plugin |
| Their price | $359/yr | ~$20/mo | $250–499/yr | Free/Pro | Free/Pro |
| Primary attack | Price + upsells + complexity | Unnecessary infrastructure | GTM container requirement | Limited platforms | Less proven |
| Secondary attack | Broken updates, ignored support | GTM expertise required | Invisible scaling costs | Meta-focused | Limited docs |
| UniPixel edge | All 5 platforms, no upsells | No container, GTM, cloud | Uses your server | 5 platforms | Established, all platforms |
| Keyword strategy | Bare brand | Modifiers only | Bare brand | Bare brand | Bare brand |

---

### Campaign 4 — Google Ads Universal Category (Sweep and Strike) — LIVE

**Technique:** Sweep and Strike / category interception
**Channel:** Google Search Ads (Search only)
**Targeting:** People searching for a solution, not a specific competitor
**Landing page:** `https://unipixelhq.com`
**Status:** Live since 24 March 2026. Same verification blocker as Campaign 3 initially; resolved 6 April 2026.

Search pool here is much larger than competitor brand terms — **this is the main volume driver.** Campaign 3 is the sniper complement.

#### Settings

| Setting | Value |
|---|---|
| Bidding | Maximise clicks |
| Daily budget | A$5.00 |
| Other settings | Same as Campaign 3 |

#### Emotional arc (all copy)

**Opportunities → Worry → Pain → Solution → Reassure**
1. Opportunity — what they could have
2. Worry — plant the seed
3. Pain — the actual problem
4. Solution — UniPixel does this
5. Reassure — easy, quick, automatic

#### Ad groups

**Server-Side Tracking** (phrase match):
`"server side tracking wordpress"`, `"server side tracking woocommerce"`, `"conversions api wordpress plugin"`, `"capi wordpress plugin"`, `"server side pixel wordpress"`

**WooCommerce Tracking** (phrase match):
`"woocommerce tracking plugin"`, `"woocommerce conversion tracking"`, `"woocommerce pixel plugin"`, `"woocommerce server side tracking"`, `"woocommerce facebook pixel plugin"`

**Platform-Specific CAPI** (phrase match):
`"facebook conversions api woocommerce"`, `"woocommerce facebook capi plugin"`, `"woocommerce meta pixel plugin"`, `"woocommerce tiktok pixel plugin"`, `"tiktok events api wordpress"`, `"google analytics woocommerce server side"`, `"pinterest conversions api wordpress"`

**Lead-Gen / Non-WooCommerce** (phrase match) — new ad group post-v2.6.6 to capture the lead-gen and non-WC WordPress audience that previously had no first-class story. Targets Pillar 3 + audience C5:
`"thank you page conversion tracking wordpress"`, `"lead tracking wordpress no gtm"`, `"meta lead event wordpress plugin"`, `"track form submission meta capi"`, `"wordpress lead gen tracking plugin"`, `"facebook lead event wordpress"`, `"google generate_lead wordpress"`, `"form submission conversion tracking wordpress"`

Headlines anchor on the new positioning: "Track Lead Without GTM", "Thank-You Page in 30 Seconds", "Set Up Lead Once — All 5 Platforms", "No CSS, No Code, No GTM", "Built for Lead-Gen Sites Too", "Newsletter Signup Across Every Platform", "Pick a Page. Pick a Conversion. Done."

Headlines and descriptions use the emotional arc. Opening hooks: "Better Data. Better Results.", "Are Your Conversions Missing?", "Every Sale Reported Accurately", "All Five APIs — One Install".

---

### Google Ads — Learnings (both campaigns)

- Google's onboarding aggressively tries to broaden targeting (AI Max, Display Network, broad match, keyword suggestions) — **refuse all of it** for precise campaigns.
- **Exact match `[brackets]` are essential** — without them Google interprets loosely and burns budget.
- Business name (Buildio) and billing entity (Elure Pty Ltd) are separate — billing entity can be updated when company name changes.
- **Advertiser verification silently blocks ad serving (discovered 24 March, NOT resolved until 6 April):** Google allows full account setup, campaign creation, billing, shows "Eligible" on everything — but serves zero impressions until advertiser identity verification completes. "Eligible" means the ad passed policy review, NOT that Google will serve it. Three tasks under Billing → Advertiser verification:
  1. Answer questions about your organisation
  2. Submit identity documents
  3. "Submit client documents" — **misleadingly named**. For solo advertisers (not agencies), it just requires selecting your own verified payment profile and clicking Finish. Does NOT require separate client paperwork.
  Task 3 remained open for 3 weeks on Campaign 3 with zero notifications and zero campaign-level warnings. **If creating a new Google Ads account: complete ALL verification tasks BEFORE creating campaigns, confirm 3/3 show green checkmarks, and remember the "Submit client documents" task applies even to solo accounts.**
- **~~"Competitor brand keywords have near-zero volume"~~ — WRONG, corrected 6 April.** The 0-impressions conclusion came from verification-blocked serving, not low volume. Actual competitor search volume is **unknown** — never tested with ads allowed to serve. Monitor all 6 ad groups now that verification is genuinely complete.
- **Phrase match for category keywords** (Campaign 4) — catches long-tail variants like "how to install meta pixel on wordpress" while staying relevant. Better than exact match for category-level intent.

---

## How the 4 campaigns work together

All four campaigns feed each other:

- Forums (organic) create brand recognition
- Meta/Insta graphics create curiosity
- Campaign 3 catches rare competitor-name searchers
- Campaign 4 catches the much larger pool of category searchers

Campaign 4 is the volume driver. Campaign 3 is sniper complement. Campaigns 1 and 2 build the trust and recognition that make Google traffic convert.

---

## Owned channels — independent web presence

> Updated 31 March 2026. UniPixel is now its own independent marketing entity with its own domain, social presence, and identity. Buildio remains the parent company for billing and business operations but is not customer-facing.

### unipixelhq.com — The Hub

UniPixel's primary web presence. Hub for everything customer-facing: landing pages, documentation, blog content, video, setup guides, and all inbound links from ads and social.

All campaigns, ad landing pages, sitelinks, and content point to unipixelhq.com. The old `buildio.dev/unipixel/` URL should redirect here.

### Meta account structure

```
Business Portfolio: Buildio (back-office — owns ad accounts, handles billing)
  └─ Ad Account: Buildio (ID: 931369629429092) — rename to "UniPixel"
      └─ Campaign 2: Meta/Instagram graphics
      └─ Any future Meta ad campaigns
  └─ Page: UniPixel (customer-facing — ads run "from" this page)
```

Buildio is the business entity behind the scenes (Elure Pty Ltd billing). The UniPixel Facebook Page is what the public sees — ads show "UniPixel" as the advertiser. Users never see "Buildio" customer-facing.

**Ad account rename:** Currently "Buildio". Rename to "UniPixel" in Ads Manager → Ad account settings. Business Portfolio stays Buildio.

### Facebook page — communications & advertising pillar

**Status (31 March 2026):** Shell page exists. Needs branding, content, activation before further ad spend scales.

The Facebook page is infrastructure, not optional social media. It affects ad performance, SEO, and trust:

1. **Ad effectiveness.** Meta's delivery system evaluates the whole advertiser profile. An ad from a shell page with no activity gets lower quality treatment. Users who check the page and find it empty bounce. Both hurt CPR and delivery reach. Current $0.03/click could degrade as spend scales.
2. **Trust checkpoint.** When someone sees a UniPixel mention in a forum, Google ad, or Reddit reply — some will search UniPixel on Facebook/Instagram. Active page = legitimacy. Dead shell page = doubt.
3. **SEO backlinks and domain authority.** A Facebook page with unipixelhq.com linked in bio and posts = link from a DA 96 domain. Every social profile pointing to unipixelhq.com builds signal consistency.
4. **Retargeting seed.** Page engagement builds custom audiences. Every organic interaction is future ad targeting data.
5. **Boosted posts = low-friction ads.** A post about a real tracking problem, boosted to WooCommerce/ecommerce interest audiences, IS a Pinpoint and Panorama or Sweep and Strike ad in organic form.

**Minimum viable page (do first):**
- Profile picture: UniPixel logo
- Cover image: branded header
- Bio: one-line value prop + unipixelhq.com link
- Category: Software / Technology
- 3–5 initial posts
- CTA button: "Learn More" → unipixelhq.com

**What to post (ongoing, low effort):**

| Post type | Frequency | Example |
|---|---|---|
| Version release notes | Each release | Rewritten as customer benefits per language rules — not dev notes |
| Tracking tips / education | 1–2x/week | "Did you know iOS privacy blocks up to 30% of conversions from reaching Meta?" |
| Links to unipixelhq.com articles | As published | Share with hook line, not just URL |
| Myth vs reality | Occasional | Sweep and Strike format as social |
| Milestone / social proof | When earned | "6 five-star reviews" — honest, no inflation |

**Boosting:** Any organically-performing post should be boosted ($5–10 extends reach meaningfully, feeds retargeting audience).

**The compound flywheel:**
```
Active page → better ad delivery → more engagement → more profile visits
  → more clicks to unipixelhq.com → more authority signals → better SEO
    → more organic installs → more reviews → better WP.org ranking
      → more installs → more content to post about → more page activity
```

---

## Growth channels — path to 10,000 installs

> **Goal:** 10,000 active installs. Then introduce Pro tier at $89/yr. See `priorities.md` § Where we're headed.

### Priority order

| # | Channel | Why first |
|---|---|---|
| 1 | **Community & forums** | Fastest path to next 100–500. Can start today. Zero cost. **Active.** |
| 2 | **YouTube videos** | Underserved niche. Long-tail discovery. Trust builder. High effort but high payoff. |
| 3 | **Docs on buildio.dev / unipixelhq.com** | Partially done. Captures search traffic. Supports all other channels. |
| 4 | **Platform partnerships** | Research phase. Pinterest first (integration is fresh and complete). |
| 5 | **Third-party advocates** | Start identifying targets. Outreach once videos and docs exist to point them to. |
| 6 | **Paid ads** | Last for bootstrap, but Google Ads already live for competitor + category interception. Meta Ads planned. |
| 7 | **GitHub presence** | Live as of May 2026. Information surface (no code), README with full positioning, releases mirror wp.org versions. SEO + trust + branded-search hygiene. See `projects/github-info-repo.md`. |

### Channel 1 — Community & forums

At ~100 installs, the fastest path to the next 500. Manual and slow per-user, but high-intent people with active problems UniPixel solves. Covered by Campaign 1.

### Channel 2 — YouTube videos

**Why:** Very little video content for WordPress-native server-side tracking without GTM. Video builds trust faster than any medium. Long-tail discovery — a good video ranks for years.

**What to make:**
- "How to set up Meta Conversions API on WooCommerce" — the money video. 5–10 min. Screen recording from install to first event firing.
- "How to set up TikTok Events API on WooCommerce" — same format
- "How to set up Pinterest Conversions API on WooCommerce" — same format
- "What is server-side tracking and why your WooCommerce store needs it" — Universal, educational
- "Track custom events on WordPress without Google Tag Manager" — demonstrates custom events UI

**Format:** Screen recordings with voiceover. No production budget needed. Authentic > polished.

**Status:** Not started. High priority.

### Channel 3 — Docs on unipixelhq.com

Not blog posts — practical, doc-style support content. Double duty: help existing users AND capture search traffic.

**What to publish:**
- Platform setup guides (Meta, Pinterest, TikTok, Google, Microsoft) — some done
- Custom events setup guide (written, ready to publish)
- Advanced Matching explanation
- Consent management setup
- Troubleshooting / FAQ
- Competitor comparison pages (UniPixel vs PYS, UniPixel vs Stape, UniPixel vs Meta for WooCommerce)

**Status:** Partially done. Ongoing.

### Channel 4 — Platform partnerships

Pinterest's ads manager setup doc recommends PixelYourSite. That's a direct pipeline of every Pinterest advertiser on WordPress being sent to a competitor. Getting listed as a recommended integration = instant credibility + stream of high-intent users.

**Targets:**
- **Pinterest** — UniPixel has full Pinterest Conversions API support. Legitimate case to be listed alongside PYS.
- **Meta** — Has a technology partners / integration directory for CAPI implementations.
- **TikTok** — Marketing Partners program for Events API integrations.
- **Google** — GA4 Measurement Protocol integrations may have a directory or certification.

**Reality check:** Some programs have minimum install thresholds. Pinterest likely most accessible (integration fresh and complete).

**Status:** Not started. High potential. Needs research per platform.

### Channel 5 — Third-party advocates

Other people recommending UniPixel > UniPixel recommending itself. One YouTube review from a WooCommerce creator with 10k subs can drive more installs than months of forum posting.

**Targets:**
- WordPress/WooCommerce YouTube reviewers
- WooCommerce agencies
- Plugin comparison bloggers ("best WooCommerce tracking plugins" listicles)
- WordPress newsletter curators (WP Weekly, MasterWP, Post Status)

**When affiliates make sense:** Once Pro tier launches, 20–30% commission gives advocates a financial reason. Before that, pitch is "this is a good product your audience will benefit from."

**Status:** Not started. Needs outreach list.

### Channel 6 — Paid advertising

Real data point: ~1,000 clicks for ~$30. That's $0.03/click.

| Click-to-install rate | Installs per $30 | Cost per install |
|---|---|---|
| 2% | 20 | $1.50 |
| 3% | 30 | $1.00 |
| 5% | 50 | $0.60 |

**Phase 1: Bootstrap (now → ~500 installs).** Buy enough installs to cross the threshold where WP.org organic discovery starts working. One-time investment. ~$300–500 to go 100 → 500 installs.

**Phase 2: Validated acquisition (500+, retention proven).** Once people download AND keep it installed, ads become validated acquisition. Key metric: retention.

**Phase 3: Revenue channel (post-Pro launch).** Once Pro is live, ads = revenue channel with real unit economics. At $1/install and 3% Pro conversion at $89/yr, that's ~$33 CAC for an $89/yr customer. Scaling ads = scaling revenue.

**Current state:** Google Ads already live (Campaigns 3 & 4). Meta planned (Campaign 2). YouTube Ads — only viable once UniPixel's own videos exist.

### Channel 7 — GitHub presence

`github.com/unipixelhq` is a public-facing information surface for the UniPixel brand. Not a code repository: plugin source ships via wp.org SVN. The repo holds the README (full positioning, comparison tables, full version changelog), 20 topic tags for GitHub Topics discovery, and Releases that mirror wp.org versions. Each release page is independently indexable.

**Why it works as a growth channel:**
- DA-96 backlink to unipixelhq.com on every page of the repo
- Branded-search hygiene: anyone googling "unipixel github" lands somewhere legitimate
- GitHub Topic pages (`/topics/server-side-tracking`, `/topics/woocommerce`, `/topics/meta-conversions-api`, etc.) are discovery surfaces in their own right
- Awesome-list eligibility (curated lists only link to repos)
- Trust signal for the technical buyer who skews dev-flavoured
- Public Issue tracker, when active, generates user-question long-tail SEO

**What it isn't:** the source of truth for plugin code. Public unobfuscated source would undermine the obfuscation-based licensing strategy in `domain-knowledge/licensing-and-protection.md`. The repo states "ships via wp.org" up front so visitors don't expect code.

**Status:** Live as of 2026-05-02. Full content surface complete. Account-level flag from launch-day automation velocity is restricting the `/releases` listing index page until cleared (direct release URLs work; sidebar widget on repo home shows all five releases). See `projects/github-info-repo.md` § Flag situation for detail.

**Maintenance cost:** ~5 minutes per wp.org release (mirror the version + changelog into a GitHub Release).

---

## Content plan

### Universal content (educate / create demand)

| Piece | Format | Audience |
|---|---|---|
| Why Your Ad Data Is Wrong | Blog post | Store owners who don't know they have a problem |
| What Is Server-Side Tracking and Why It Matters | Blog + video | People who've heard the term but don't understand |
| The Ad Blocker Problem: How Your Data Disappears | Blog / social | Broad awareness — shareable, stat-driven |
| Meta Keeps Telling You to Set Up CAPI — Here's What That Means | Blog + video | People who've seen Meta warnings but don't know what to do |

### Competitive content (convert intent)

| Piece | Format | Audience |
|---|---|---|
| How to Set Up Meta CAPI on WooCommerce — No Server Container Needed | Blog + video | People actively searching for the fix |
| Server-Side Tracking Without Paying for Server Containers | Blog post | People who bounced on infrastructure complexity |
| UniPixel vs Stape — Simpler, No Container | Blog post | People evaluating Stape or managing GTM server hosting |
| PixelYourSite vs UniPixel | Blog post | Comparison shoppers |
| Alternatives to PixelYourSite Pro | Blog post | People searching PYS alternatives |
| Manage Custom Events Without Google Tag Manager | Blog post | People who find GTM too complex |
| UniPixel vs Meta for WooCommerce | Blog post | People frustrated with Meta's plugin |

Already published: **Stape Alternatives** article (`stape-alternatives.md`).

---

## WordPress.org readme — display rules & operations

### Character limits and search weight

| Field | Limit | Search Weight | Where It Appears |
|---|---|---|---|
| Title | No hard limit (keep reasonable) | Highest | Search results heading, plugin page h1 |
| Short description | **150 chars max** | High | Search result cards, wp-admin Add Plugins screen |
| Tags | 5 displayed by directory, up to 12 total | Medium | Directory search index (first 5), Google indexing (6–12) |
| Full description | ~1,500 words before truncation | Medium | Plugin page main content |

### Search ranking factors (in order)

1. Title keywords (highest)
2. Short description keywords (high)
3. Ratings/reviews (0 reviews = treated as 2.5 stars) (very high)
4. Active install count (high)
5. Full description + tags (medium)
6. Support ticket resolution rate (medium)
7. Last update recency (180+ days = penalty) (medium)

### Tag update rules (sticky behaviour)

- Tags read from `/tags/{version}/readme.txt` in SVN, **not trunk**
- Editing trunk readme.txt does NOT update tags if `Stable tag` points to a version number
- To update tags: edit readme.txt inside the current stable tag folder, or release a new version
- Cache: ~10 min for search, up to 6 hours for page display

### Current readme.txt issues (release-log.md #12)

| Issue | Detail |
|---|---|
| Short description over limit | 165 chars — silently truncated mid-word |
| Typo in short description | "...Google using." — orphaned word |
| Microsoft missing | Not in title, short description, barely in body |
| WooCommerce missing from title | Huge search term gap |
| Only 4 tags used | 5th slot empty; tags 6–12 (Google-indexed) unused |
| Tags too specific | "Facebook Conversion API" — nobody searches that exact phrase |
| Setup section too long | Credentials take 60% of readme. Should be docs or FAQ. |
| Typos in body | "additioal", "mintues", "inWordPress", "debgugging", "vents", "inlcuding" |
| Only 3 screenshots | More = more visual trust |

### Readme options under consideration

**Title (current: too long, WooCommerce missing):**
A. `UniPixel: Meta Conversion API, TikTok & Google Server-Side Tracking for WooCommerce` (82)
B. `UniPixel: Free Server-Side Conversion Tracking for Meta, Google, TikTok & Microsoft` (80) *(violates "don't lead with free" language rule)*
C. `UniPixel: Server-Side Conversion API Tracking for Meta & Google` (65)

**Short description (150 char max):**
A. `Server-side tracking for Meta CAPI, GA4 and TikTok from your own WordPress server. No external cloud, no GTM setup. WooCommerce and custom events.` (148)
B. `Meta Conversion API, GA4 Measurement Protocol and TikTok Events API built in. Server-side and client-side. No cloud hosting or GTM required.` (146)
C. `Connect Meta, Google, TikTok and Microsoft with server-side and client-side event tracking. WooCommerce events, custom events and consent included.` (149)
D. `Send conversion events from your WordPress server to Meta, Google, TikTok and Microsoft. No extra cloud, no GTM. Includes WooCommerce tracking.` (143)

**Tags:**
A. `meta conversion api, server-side tracking, tiktok pixel, facebook capi, woocommerce pixel`
B. `meta conversion api, facebook server side, tiktok events api, conversion tracking, google measurement protocol`
C. Keep current + fill 5th slot: `Meta Pixel, Facebook Conversion API, TikTok Events API, Server-side Tracking, meta capi`

### Operations

- Regular updates every 4–8 weeks to avoid the 180-day ranking penalty
- Grow screenshots beyond current 3
- Responsive support forum replies — signal to ranking algorithm

---

## Perch and Poach — PixelYourSite competitive intelligence

> Background material for Campaign 1 (forums) and Campaign 3 (Google Ads PYS ad group). PYS is market leader (18.9M total downloads) with a wide satisfaction gap — 4.3/5 on WP.org, **2.3/5 on Trustpilot** with 75% one-star reviews.

### What PYS offers

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

**Pricing (May 2025 restructure):** Starter $359/yr (1 site), Advanced $399/yr (10 sites, includes Pinterest + Bing add-ons), Agency $999/yr (100 sites).

**Key fact:** Meta CAPI is in their free version. **"No CAPI" is NOT an angle against PYS.** The angle is: everything beyond Meta + GA4 costs $359+/year.

### The 9 documented pain points (ranked by frequency)

1. **Support is dead.** Weeks without replies. Refunds promised but ignored. 15 pages of unresolved WP.org threads. Trustpilot 2.3/5, 75% one-star.
2. **Updates break sites.** v9.5.2 shipped PHP 8 warnings to production. v12.4.1 Pro froze entire sites — plugin loaded external requests that locked browsers.
3. **Fake conversion data — $40K loss.** One user lost $40,000+ ad spend because PYS sent phantom purchase events to Facebook. Public WP.org thread. Most damaging single complaint in the ecosystem.
4. **Aggressive upselling.** Dashboard flooded with upgrade nags from installation. *"Plugins are spamming WordPress dashboard non-stop, bad UX for settings."*
5. **Caching kills tracking silently.** If a cache plugin serves a bot-generated page, PYS suppresses itself on that cached version. All real visitors get the cached page — with no tracking. PYS's own docs acknowledge. No clean fix.
6. **Confusing setup.** *"Navigate unclear settings and click non-intuitive buttons to access basic fields like entering a Meta Pixel ID."*
7. **Consent plugin conflicts.** Complianz blocks PYS even after visitors grant consent. Disabling Complianz fixes tracking but removes GDPR compliance. Lose-lose.
8. **Purchase tracking misses.** Purchase event only fires when customer views the thank-you page. If they close the browser before it loads, purchase never tracked. Structural.
9. **Price resistance.** $359/yr minimum for full stack. 6+ piracy sites offer cracked Pro — signal that users want the features but resist the price. Pricing restructure May 2025 raised entry-level prices.

### Sharpest hooks

| Hook | Pain targeted | Source |
|---|---|---|
| "Tracking 5 platforms shouldn't cost $359/year" | Multi-platform paywall | PYS pricing page |
| "15 pages of unanswered support threads" | Abandonment anxiety | WP.org forum |
| "One plugin update shouldn't freeze your entire site" | Reliability fear | v12.4.1 incident |
| "Your tracking plugin sent sales that never happened" | Data trust destruction | $40K thread |
| "You shouldn't need a 20-minute tutorial to enter a Pixel ID" | Complexity frustration | Setup complaints |
| "Your cache is silently killing your tracking and you don't know it" | Invisible data loss | Caching bug |
| "Your purchase tracking only works if the customer waits for the thank-you page" | Structural weakness | Thank-you dependency |
| "Dashboard full of upgrade nags before you've even set up a pixel" | Upsell fatigue | Install experience |

> **Key rule: hooks expose friction, don't name-call.** Framing is always "this shouldn't be this hard" or "there's a simpler way" — never "PixelYourSite is bad." In forum replies, don't reference these hooks as attack lines — just be helpful and let the comparison speak for itself. In Google Ads and content, hooks become headlines.

### Execution channels (already active in Campaigns)

- **Campaign 1:** WP.org support forum (15 pages of unresolved threads)
- **Campaign 3:** Google Ads — PYS ad group
- **Content/SEO:** "PixelYourSite vs UniPixel" comparison page, "How to avoid phantom conversions in WooCommerce" targets the $40K story without naming PYS, "WooCommerce server-side tracking without a $359/year plugin" targets price pain
- **Reddit / FB groups:** r/woocommerce, r/PPC, r/FacebookAds threads mentioning PYS frustrations
- **YouTube:** pre-roll ads on PYS tutorial videos (20-min setup guides = complexity signal)

---

## Ad copy working drafts

Sample ad copy aligning with principles — use or iterate.

**Ad 1 — Data angle:**
- Hook: *Your ad data is wrong.*
- Context: *Browsers are blocking your results before they reach your ad platforms.*
- Rainbow: *UniPixel sends it directly from your WordPress website. Install and it's handled.*

**Ad 2 — Events angle:**
- Hook: *Are you tracking what matters?*
- Context: *Platforms don't want your page views. They want the events you care about.*
- Rainbow: *UniPixel tracks and sends them server-side from your WordPress website. No GTM.*

**Ad 3 — Custom events:**
- Hook: *Track any interaction on your site.*
- Context: *Buttons, forms, popups — if it happens on your site, you should be measuring it.*
- Rainbow: *UniPixel lets you set up custom events and send them server-side. No code. No GTM.*
