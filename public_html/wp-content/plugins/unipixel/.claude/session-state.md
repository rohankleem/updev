# Session State

**Last updated:** 2026-03-24

> **Update protocol (for Claude):** When Rohan says "update session state", rewrite this file entirely:
> - Shift "What we worked on" → "Where we came from"
> - Write fresh "What we worked on" covering this session's decisions, fixes, and code changes
> - Write fresh "Where we need to go" with enough detail to hit the ground running next session
> - Update the "Last updated" date

> **Session start protocol:** When a new session opens, read this file, then:
> 1. Confirm you are aware of the project structure from CLAUDE.md — CWD is the plugin folder, all Claude files are here, scope rule is understood, deployment pipeline is understood. One sentence, no elaboration.
> 2. Summarise what was worked on last session and what the next task is. Be specific.
> 3. Do NOT just say "session state loaded" or "everything checks out." Do NOT re-explain the project structure. Go straight to the work context.

---

## Where We Came From

- Microsoft Conversions API (CAPI) — full implementation (v2.6.0)
- 16 new WooCommerce pipeline files, complete CAPI integration
- Microsoft setup documentation for buildio.dev
- Client-side PageView confirmed working, all 4 platforms fire simultaneously
- v2.6.0 ready for obf export

---

## What We Worked On

**Session date:** 2026-03-24

### Google Ads — diagnosis and fixes

**Zero impressions across both campaigns (14–24 March):** Investigated why both "UniPixel | Perch and Poach" (Campaign 3) and "UniPixel | Universal | Sweep and Strike" (Campaign 4) had zero impressions, zero clicks, A$0.00 cost over 9+ days.

**Root cause: Incomplete advertiser verification.** Google allows full account setup, campaign creation, billing, and shows "Eligible" status on everything — but silently serves zero impressions until identity verification is complete. No warning, no error, no "action required" badge anywhere on the campaigns page. "Eligible" is genuinely misleading — it means the ad passed policy review, NOT that Google will serve it. Found under Billing → Advertiser verification. Two tasks were incomplete: "Provide your agency's info" and "Provide client's info" (both just need your own details — Google's agency/client language is confusing for solo advertisers). Both completed and verified 24 March 2026: "Advertiser identity verified." Ads should begin serving within hours.

**Secondary finding: Competitor brand keywords have near-zero search volume.** Several Stape keywords flagged "Not eligible — Low search volume." All 5 competitor ad groups (PYS, Stape, Conversios, PixelCat, Pixelavo) had 0 impressions. WordPress plugin brand names are too niche for Google Search. Campaign 3 costs nothing at zero impressions — keep running as sniper, but Campaign 4 (universal) is the real volume driver.

### New ad group: Meta Pixel WordPress (added to Campaign 3)

**Perch and poach on Meta's own pixel.** People searching "meta pixel wordpress" are trying to install Meta's clunky JS snippet — intercept them. Keywords use phrase match (not exact) to catch variants. Attack angle: Meta's pixel is buggy, clunky, limited to one platform. UniPixel does Meta properly plus four more. Does NOT claim Meta lacks server-side (CAPI exists) — targets setup complexity, 2.7-star official plugin rating, single-platform limitation.

### Campaign 4 ad copy written

Three ad groups with full headlines, descriptions, callouts:
1. **Server-Side Tracking** — opportunity/worry/pain/solution/reassure arc
2. **WooCommerce Tracking** — same arc, WooCommerce-specific
3. **Platform-Specific CAPI** — "you came for one API, get all five"

All copy follows the Opportunities → Worry → Pain → Solution → Reassure emotional framework.

### Marketing docs updated

- Campaign 3 status updated (all 6 ad groups LIVE including Meta Pixel)
- Campaign 4 fully documented (settings, ad groups, keywords, copy)
- Competitor ad group matrix statuses all updated to LIVE
- Learnings section expanded with verification discovery, low search volume finding, phrase match guidance
- "How campaigns work together" updated from 3 to 4 campaigns

---

## Where We Need To Go

### IMMEDIATE — Monitor ad serving post-verification

Verification completed and confirmed 24 March ("Advertiser identity verified"). Check within 24-48 hours (25-26 March):
- Are impressions appearing on either campaign?
- Campaign 4 was in "Bid strategy learning" — has it moved to active?
- Campaign 3 has 6 ad groups now (5 competitors + Meta Pixel) — all showing Eligible
- If still zero after 48 hours, check: billing credit balance (manual payment account, A$20 loaded 13 March — balance showed A$0.00), ad approval status per ad group
- **"Eligible" does NOT mean serving** — learned this the hard way. Check actual impression counts.

### IMMEDIATE — Deploy v2.6.0

Obf export ready. Smart quotes check passed. Ready for export and WordPress.org tag. (Carried over from last session — not yet done.)

### Still on the radar (product)

- **PHP-side validation gap** — `serverside_global_enabled` ON with empty credentials = silent API failures
- **Setup wizard / onboarding flow** — Tier 2
- **Custom events wizard** — Tier 2
- **Event diagnostics dashboard** — Tier 2
- **external_id** — never populated, needs strategy decision
- **`send_server_log_response` not in CREATE TABLE** — exists via migration only
- **Full Pinterest WooCommerce testing** — AddToCart, InitiateCheckout, Purchase still untested
- **Microsoft CAPI** — fully coded but untested against live endpoint (token generation not self-service yet)

### Growth execution

| Priority | Channel | Status | Next action |
|---|---|---|---|
| 1 | **Community & forums (Reddit)** | **Active** | Keep posting 3-5 helpful replies/week |
| 1b | **Google Ads** | **LIVE — both campaigns** | Monitor post-verification, check impressions 25-26 March |
| 1c | **Meta/Insta Ads** | Running | Review creatives and results |
| 2 | **YouTube videos** | Not started | Record screen capture tutorials |
| 3 | **Docs on buildio.dev** | Partially done | Google setup guide needed |
| 4 | **Platform partnerships** | Not started | Research partner program requirements |
| 5 | **Third-party advocates** | Not started | Identify reviewers, agencies |
