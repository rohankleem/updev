# Release Log & Backlog

Single source of truth for release history, what's staged for the next release, and the product backlog (buckets + full table).

---

## Current State

- **Live on wordpress.org:** `v2.5.4`
- **Source / pending version (`unipixel.php`):** `v2.6.3`
- **Pending action:** deploy the staged version (v2.6.x) to wordpress.org — obf export ready, smart-quote check passed last session. See `session-state-rohan.md` for current blockers.

> When a release ships, update this block, move the "Staged for next release" items into a stamped entry in "Released History", and bump the version in the four release-gate files (see `/CLAUDE.md` § Release Gate).

---

## Staged for next release (Done since v2.5.4)

> This is the staging area for unreleased work. When it's time to release, review this list, decide on a version number (patch/minor/major), then update readme, marketing, backlog accordingly. Rohan chooses the version number based on the weight of changes.

### Microsoft Conversions API (CAPI) — full implementation
- Server-first WooCommerce pipeline for all 4 events (Purchase, AddToCart, Checkout, ViewContent)
- Client-first AJAX callback for PageView and custom events
- `msclkid` cookie capture (90-day retention) from URL params
- CAPI endpoint: `capi.uet.microsoft.com/v1/{tagID}/events` with Bearer auth
- Deduplication via shared `eventId` between UET push and CAPI call
- Consent mode integration — `adStorageConsent` (granted/denied) sent server-side, UET consent API set client-side
- Admin UI: events page rewritten (WooCommerce event table, server-side columns, recommended settings)
- Admin UI: help icon popovers on setup and events pages
- "Apply Recommended Settings" one-click preset for Microsoft (both client+server on, log response for Purchase)
- Removed legacy JS blocks that force-disabled server-side toggles for Microsoft
- **⚠️ CAPI prototype:** Token access not yet self-service in Microsoft Advertising. Server-side untested against live endpoint. Client-side UET confirmed working. Do not advertise CAPI until verified.

### AddToCart event quality improvement
- Event quality improvements for AddToCart event — better handling of different add-to-cart methods across WooCommerce themes.
- AJAX add-to-cart (shop/archive pages) now correctly fires client-side pixels for all 5 platforms via WooCommerce fragments (see `domain-knowledge/platform-discoveries.md` § ATC-001).
- Consolidated internal user identifier logic for transient-based event relay.

### Checkout (InitiateCheckout) event firing accuracy
- Replaced commented-out transient dedup (1-min TTL, md5 IP+UA identifier) with WooCommerce session-based dedup tied to cart hash.
- Event now fires once per genuine checkout intent — page refreshes, payment failure redirects, and back-button navigation no longer re-fire.
- Cart changes between checkout visits correctly trigger a re-fire (new intent).
- All 5 platforms (Meta, Google, TikTok, Pinterest, Microsoft) benefit.

### Files changed (summary)
- **New/rewritten:** `trackers/microsoft-handler.php`, `js/clientfirst-watch-and-send-microsoft.js`, `admin/page-microsoft-events.php`
- **Major changes:** `functions/send-server-event.php`, `functions/hooks.php`, `functions/unipixel-functions.php`, `trackers/microsoft-enqueue.php`, `js/pixel-microsoft.js`, `js/unipixel-consent.js`, `config/schema.php`, `admin/page-microsoft-setup.php`, `admin/js/ajax-event-settings.js`, `admin/js/unipixel-apply-recommended.js`
- **WooCommerce pipeline:** 16 files in `woocomm-hook-handling/` updated with Microsoft blocks
- **Meta:** `unipixel.php` (version/description), `readme.txt` (Microsoft throughout)

---

## Backlog — Buckets

| Bucket | What it means |
|---|---|
| **Event Quality** | Making the data platforms receive as good as possible — higher match scores, better readings, stronger algorithm performance. Core value of the plugin. |
| **Onboarding** | Getting new users from install to working tracking with minimum friction. Reducing drop-off in the first 10 minutes. |
| **UX** | Helping existing users understand, trust, and use the plugin effectively after initial setup. |
| **Platform Coverage** | Expanding what platforms and event types UniPixel supports. More reach. |
| **Commercial** | Monetisation, licensing, registration, IP protection. Future — depends on download traction (need 1000s). |
| **Growth** | Marketing, content, WordPress.org optimisation. Getting people to the plugin. |
| **Housekeeping** | Tech debt, schema consistency, deployment. |

---

## Full Backlog

| # | Feature | Buckets | Effort | Status | Notes |
|---|---|---|---|---|---|
| 1 | Setup wizard / onboarding flow | Onboarding | Days | Not started | Most deactivations happen in first 10 minutes. Guided first-run: connect platform, verify it works. |
| 2 | Custom events wizard (visual element picker) | Onboarding, UX | Days | Not started | Current UI requires CSS selectors — unintuitive. Needs visual picker, templates, validation, test/preview. Stopgap docs written. |
| 3 | Event diagnostics dashboard | UX, Onboarding | Days | Not started | "Is it working?" — health screen showing last event, success/fail counts, connection status per platform. Data already in `unipixel_event_log`. |
| 4 | Microsoft WooCommerce pipeline (server-first) | Platform Coverage, Event Quality | Days | **Done (staged, v2.6.0 work)** | Full CAPI implementation. Server-side untested against live endpoint (token access not self-service). Client-side UET confirmed working. |
| 5 | Billing address fields in user_data | Event Quality | Hours | Not started | Available from WooCommerce, not currently sent. Improves match quality for Meta/TikTok. |
| 6 | external_id population | Event Quality | Hours | **Assessed, deprioritised** | Not a quality gap for guest-checkout WooCommerce. See `domain-knowledge/platform-discoveries.md` § META-002. |
| 7 | Additional platforms (Snapchat, LinkedIn) | Platform Coverage | Weeks | Not started | Pro tier candidates. Matters to agencies, not a blocker for solo store owners. Pinterest done (v2.5.2). |
| 8 | PHP validation — empty access token when server-side enabled | Onboarding, UX | Hours | Not started | Users can enable server-side with no access token — API call just fails silently. Add validation or admin notice. |
| 9 | `send_server_log_response` in CREATE TABLE definition | Housekeeping | Minutes | Not started | Column exists via migration only, not in dbDelta CREATE TABLE. Not a bug but inconsistent. |
| 10 | Email/phone on client-first events (PageView/custom) | Event Quality | Hours | Not started | Only sent for WooCommerce events currently. Client-first events (PageView, custom clicks) don't include user PII. |
| 11 | CMP auto-detection | Onboarding, UX | Hours | Not started | Detect which CMP is active and pre-select it, instead of user choosing from dropdown. Parsers already run regardless — this is just UI convenience. |
| 12 | Readme rewrite (title, short description, tags, typos) | Growth | Hours | Not started | Multiple issues: short desc over 150 chars, typos, Microsoft missing, WooCommerce missing from title, only 4/12 tags used. Positioning lives in `marketing-knowledge/positioning.md`. |
| 13 | Screenshots for WordPress.org | Growth | Hours | Not started | Only 3 screenshots currently. More = more visual trust. |
| 14 | Registration / signup on activation | Commercial, Onboarding | Days | Not started | Prompt on install: collect domain + testing domain, create instance on licensing server. Details in `domain-knowledge/licensing-and-protection.md`. |
| 15 | Admin monitoring dashboard (server-side) | Commercial | Days–Weeks | Not started | See all installs: domain, status, version, last seen. Remote deactivation. Usage monitoring. |
| 16 | Soft limits / freemium gating | Commercial | Days | Not started | Options: time-limited free tier, event volume cap, or platform cap. Decision depends on download traction. |
| 17 | License-gated updates (Freemius or similar) | Commercial | Days | Not started | Primary protection strategy. No valid license = no new versions. Plugin degrades as platform APIs change. |
| 18 | "UNREGISTERED" admin notice | Commercial | Hours | Not started | Visible, embarrassing notice for unregistered/expired installs. Nudge toward registration. |
| 19 | Blog content (Universal + Competitive pillars) | Growth | Ongoing | Not started | Content plan in `marketing-knowledge/campaigns.md`. |
| 20 | YouTube tutorials | Growth | Ongoing | Not started | Screen-recorded setup tutorials. 5–10 min videos. Builds trust. |
| 21 | Community seeding | Growth | Ongoing | **Active** | Facebook groups, Reddit, WP.org forums, WooCommerce Slack. 3–5 helpful replies/week. |
| 22 | Deploy 2.5.1 to WordPress.org | Housekeeping | Hours | **Done** | Deployed. |
| 23 | Commit local git changes | Housekeeping | Minutes | Pending | Multiple sessions of plugin work need committing. Waiting on instruction. |
| 24 | Deploy v2.6.x to WordPress.org | Housekeeping | Hours | **IMMEDIATE** | Microsoft CAPI + AddToCart fragments release. Source at v2.6.3, obf export ready. See session-state. |
| 25 | AddToBasket quality improvement | Event Quality | Days | **Done (staged)** | AJAX add-to-cart client pixel via fragment collector. Full detail in `domain-knowledge/platform-discoveries.md` § ATC-001, ATC-002. |
| 26 | Remove jQuery dependency from frontend JS | Housekeeping | Days | Not started | Frontend scripts use jQuery for `$(document).ready()` and `$.post()` only. Replace with vanilla `DOMContentLoaded` and `fetch()`. Removes 30KB dependency. Low priority — WooCommerce sites always have jQuery. Immediate fix applied: `jquery` added as dependency of `unipixel-common`. |
| 27 | Stored Event Logs UX improvements | UX | Hours–Days | Not started | (a) No explanation of what logs are, what they're for — needs intro text. (b) Hard to find events — no filtering by event type / platform / date. (c) Logging requires "Log Server-side Response" ON per event — easy to miss, logs empty by default. See `domain-knowledge/event-logs.md` for the guide's framing. |
| 28 | Multi-tier click ID persistence | Event Quality | Days | Not started | Click IDs currently in single cookie = single point of failure. Full design: `projects/multi-tier-clickid-persistence.md`. Triggered by support case (Agence Amar). |
| 29 | TikTok expanded event coverage (vertical/funnel events) | Platform Coverage, Event Quality | Hours–Days | Not started | Priority: AddPaymentInfo first (universal e-commerce signal, completes the TikTok funnel), then CompleteRegistration + SubmitForm for lead-gen verticals. Triggered by jerseysystem.com feedback. |

---

## Bucket Summary View

### Event Quality
| # | Feature | Effort | Status |
|---|---|---|---|
| 5 | Billing address fields in user_data | Hours | Not started |
| 6 | external_id population | Hours | Assessed, deprioritised |
| 10 | Email/phone on client-first events | Hours | Not started |
| 28 | Multi-tier click ID persistence | Days | Not started |
| 29 | TikTok expanded event coverage | Hours–Days | Not started |
| 4 | Microsoft WooCommerce pipeline | Days | Done (staged) |
| 25 | AddToBasket quality improvement | Days | Done (staged) |

### Onboarding
| # | Feature | Effort | Status |
|---|---|---|---|
| 1 | Setup wizard / onboarding flow | Days | Not started |
| 2 | Custom events wizard | Days | Not started |
| 3 | Event diagnostics dashboard | Days | Not started |
| 8 | PHP validation — empty access token | Hours | Not started |
| 11 | CMP auto-detection | Hours | Not started |
| 14 | Registration / signup on activation | Days | Not started |

### UX
| # | Feature | Effort | Status |
|---|---|---|---|
| 2 | Custom events wizard | Days | Not started |
| 3 | Event diagnostics dashboard | Days | Not started |
| 8 | PHP validation — empty access token | Hours | Not started |
| 11 | CMP auto-detection | Hours | Not started |
| 27 | Stored Event Logs UX improvements | Hours–Days | Not started |

### Platform Coverage
| # | Feature | Effort | Status |
|---|---|---|---|
| 4 | Microsoft WooCommerce pipeline | Days | Done (staged) |
| 7 | Additional platforms (Snapchat, LinkedIn) | Weeks | Not started |
| 29 | TikTok expanded event coverage | Hours–Days | Not started |

### Commercial
| # | Feature | Effort | Status |
|---|---|---|---|
| 14 | Registration / signup on activation | Days | Not started |
| 15 | Admin monitoring dashboard | Days–Weeks | Not started |
| 16 | Soft limits / freemium gating | Days | Not started |
| 17 | License-gated updates | Days | Not started |
| 18 | "UNREGISTERED" admin notice | Hours | Not started |

### Growth
| # | Feature | Effort | Status |
|---|---|---|---|
| 12 | Readme rewrite | Hours | Not started |
| 13 | Screenshots for WordPress.org | Hours | Not started |
| 19 | Blog content | Ongoing | Not started |
| 20 | YouTube tutorials | Ongoing | Not started |
| 21 | Community seeding | Ongoing | **Active** |

### Housekeeping
| # | Feature | Effort | Status |
|---|---|---|---|
| 9 | `send_server_log_response` in CREATE TABLE | Minutes | Not started |
| 22 | Deploy 2.5.1 | Hours | Done |
| 23 | Commit local git changes | Minutes | Pending |
| 24 | Deploy v2.6.x to WordPress.org | Hours | **IMMEDIATE** |
| 26 | Remove jQuery dependency from frontend JS | Days | Not started |

---

## Released History

> Populated when a release ships. Each block should capture: version number, ship date, headline changes, files touched (summary), notable post-release observations.

- **v2.5.4** — (last wp.org release) — fixed 15 PHP files with U+2018/U+2019 smart quotes from v2.5.3 that had caused fatal errors on all WooCommerce events. See `domain-knowledge/platform-discoveries.md` § RQ-001.
