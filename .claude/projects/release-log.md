# Release Log & Backlog

Single source of truth for release history, what's staged for the next release, and the product backlog (buckets + full table).

---

## Current State

- **Live on wordpress.org:** `v2.6.5`
- **Source version (`unipixel.php`):** `v2.6.6`
- **Pending action:** deploy v2.6.6 to wordpress.org. Centralised Event Manager release (Phase 1 URL trigger, Phase 2 standard event name dropdowns, Phase 3 cross-platform conversion builder with grouping). Obf export + SVN commit pending.

> When a release ships, update this block, move the "Staged for next release" items into a stamped entry in "Released History", and bump the version in the four release-gate files (see `/CLAUDE.md` § Release Gate).

---

## Staged for next release (Done since v2.6.6)

> This is the staging area for unreleased work. When it's time to release, review this list, decide on a version number (patch/minor/major), then update readme, marketing, backlog accordingly. Rohan chooses the version number based on the weight of changes.

_(empty — v2.6.6 ships now)_

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
| 24 | Deploy v2.6.x to WordPress.org | Housekeeping | Hours | **Done** | Shipped as v2.6.3. |
| 25 | AddToBasket quality improvement | Event Quality | Days | **Done (staged)** | AJAX add-to-cart client pixel via fragment collector. Full detail in `domain-knowledge/platform-discoveries.md` § ATC-001, ATC-002. |
| 26 | Remove jQuery dependency from frontend JS | Housekeeping | Days | Not started | Frontend scripts use jQuery for `$(document).ready()` and `$.post()` only. Replace with vanilla `DOMContentLoaded` and `fetch()`. Removes 30KB dependency. Low priority — WooCommerce sites always have jQuery. Immediate fix applied: `jquery` added as dependency of `unipixel-common`. |
| 27 | Stored Event Logs UX improvements | UX | Hours–Days | Not started | (a) No explanation of what logs are, what they're for — needs intro text. (b) Hard to find events — no filtering by event type / platform / date. (c) Logging requires "Log Server-side Response" ON per event — easy to miss, logs empty by default. See `domain-knowledge/event-logs.md` for the guide's framing. |
| 28 | Multi-tier click ID persistence | Event Quality | Days | Not started | Click IDs currently in single cookie = single point of failure. Full design: `projects/multi-tier-clickid-persistence.md`. Triggered by support case (Agence Amar). |
| 29 | TikTok expanded event coverage (vertical/funnel events) | Platform Coverage, Event Quality | Hours–Days | Not started | Priority: AddPaymentInfo first (universal e-commerce signal, completes the TikTok funnel), then CompleteRegistration + SubmitForm for lead-gen verticals. Triggered by jerseysystem.com feedback. |
| 30 | Meta test event code field | Event Quality, UX | Hours | Not started | No field to paste the Events Manager "Test Events" code — users can't verify server-side events land in Meta's Test Events tab, only in the plugin's local log. Add input on Meta setup page, pass as `test_event_code` in CAPI payload (opt-in, per session). Source: user feedback review. |
| 31 | Consent popup localization (multi-language + editable) | UX, Platform Coverage | Days | Not started | Built-in consent popup is single-language only AND strings are hardcoded, so even single-language stores can't change wording. Dealbreaker for multi-region stores. Full spec: `projects/consent-popup-i18n.md` — covers admin UI, `.po/.mo` + override hybrid, security (kses + escaping), phased delivery (Phase 1 = editable English, Phase 2 = multi-language, Phase 3 = polish). Source: user feedback review. |
| 32 | Refresh "Cookie Consent & Tracking" docs article | Growth | Hours | Not started | Article at `unipixelhq.com/unipixel-docs/` was written before v2.6.4 + v2.6.5 work. Now significantly understates capabilities — needs to cover the 18-language popup, per-language editable wording, 5 layout styles, optional non-blocking mode, and Reject all toggle. Apply voice rules from `marketing-knowledge/writing-style.md`. Help-icon popovers in the plugin admin point at this URL, so it's high-traffic. |

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
| 30 | Meta test event code field | Hours | Not started |
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
| 30 | Meta test event code field | Hours | Not started |
| 31 | Consent popup localization | Days | Not started |

### Platform Coverage
| # | Feature | Effort | Status |
|---|---|---|---|
| 4 | Microsoft WooCommerce pipeline | Days | Done (staged) |
| 7 | Additional platforms (Snapchat, LinkedIn) | Weeks | Not started |
| 29 | TikTok expanded event coverage | Hours–Days | Not started |
| 31 | Consent popup localization | Days | Not started |

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
| 24 | Deploy v2.6.x to WordPress.org | Hours | Done |
| 26 | Remove jQuery dependency from frontend JS | Days | Not started |

---

## Released History

> Populated when a release ships. Each block should capture: version number, ship date, headline changes, files touched (summary), notable post-release observations.

- **v2.6.6** (2026-04-30) — Centralised Event Manager release. New top-level admin page for cross-platform conversion creation: pick a conceptual event (Lead, Newsletter Signup, etc.) and UniPixel fills in each platform's standard event name automatically. New URL-based trigger for custom events (fire on thank-you pages, lead pages, post-checkout pages with wildcard URL patterns). Standard event name dropdowns when defining custom events per platform. Page/URL picker reusable component. Fire-once-per-session guard for URL events. G-001 mutex enforced inline (Google client OR server, not both, except Purchase). Schema: new `unipixel_conversion_groups` table + `conversion_group_id` link column on `unipixel_events_settings`. Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.5** — popup style options (5 layouts: centred / top bar / bottom bar / bottom-left / bottom-right corner card), optional Reject all button (off by default, translated into all 18 locales), CookieAdmin (Softaculous) third-party CMP support, mobile-responsive popup (buttons stack on phones), popup animation centering fix, auto cache-bust for popup assets via filemtime suffix, plus admin polish (Test the popup section, Languages save-mode hint, dropdown label rename, plugin homepage URL update). Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.4** — multi-language consent popup (18 bundled locales + admin Languages & Content override accordion + popup language control). Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.0–2.6.3** — Microsoft CAPI full implementation, AddToCart fragment pixel for AJAX add-to-cart, InitiateCheckout session-based dedup, plus 2.6.1 / 2.6.3 compatibility fixes. Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.5.4** — fixed 15 PHP files with U+2018/U+2019 smart quotes from v2.5.3 that had caused fatal errors on all WooCommerce events. See `domain-knowledge/platform-discoveries.md` § RQ-001.
