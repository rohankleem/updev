# Release Log & Backlog

Single source of truth for release history, what's staged for the next release, and the product backlog (buckets + full table).

---

## Current State

- **Releasing now:** `v2.6.9` — gate files bumped (2026-06-15), obfuscated export built, staged into SVN `tags/2.6.9/` + `trunk/readme.txt`. **Awaiting Rohan's TortoiseSVN commit.**
- **`v2.6.9` bundles:** the staged token-acquisition UX initiative (wizards, Test Connection, client/server split, status strips, home badges, plain-English log badges) + the `log_time` index (originally the 2.6.8 hotfix) + three fixes made 2026-06-15 (log-prune now actually trims — the `cleanup_logs()` DELETE was inert; platform-settings save JSON error on hosts that print PHP warnings; server-side PageView setting clobbered on every setup save). **Supersedes the 2.6.8 hotfix** (2.6.9 contains the index).
- **Still to do for 2.6.9:** (1) Rohan TortoiseSVN commit (`tags/2.6.9/` + `trunk/`); (2) bump `UPHQ_PLUGIN_VERSION` → 2.6.9 on uphq + deploy (gate file #5); (3) commit the `main` source bump + the 2026-06-15 fixes to git.

> When a release ships, update this block, move the "Staged for next release" items into a stamped entry in "Released History", and bump the version in the four release-gate files (see `/CLAUDE.md` § Release Gate).

---

## Staged for next release (Done since v2.6.7)

> **Cleared into v2.6.9 (2026-06-15).** The token-acquisition UX initiative below shipped as part of v2.6.9 — see Released History. This section is the staging area for the *next* unreleased work; add new items here as they land.

- **Token-acquisition UX initiative — all 8 phases shipped (#34 + subsumes #8 + #30 + #1).** Full plan and per-phase detail: `projects/token-acquisition-ux.md`. Headline pieces:
  - **Test Connection button on every platform setup page** (Meta / Google / TikTok / Pinterest / Microsoft). Each validates against the platform's real API and surfaces platform-specific diagnostic feedback (Meta parses `debug_token` scopes/expiry/error codes; Google fires `debug_mode:1` event to DebugView; TikTok uses `test_event_code` to land in Test Events tab; Pinterest two-call validation of token + ad-account access; Microsoft parses structured CAPI error body).
  - **Three-state status indicator strip** on each setup page (Not Started / Pasted Unverified / Connected) with verified-X-ago timestamp on the Connected variant.
  - **Home dashboard badges** per platform card mirroring the same state machine.
  - **Setup wizards** (modal, 7 steps each) on every platform with platform-specific "what to ignore" copy + deep links to the right surfaces.
  - **Plain-English response badges in Stored Event Logs** — raw HTTP codes translated to "Sent OK / Token problem / Data problem / Rate limited / Not logged / Client-side, no response", with the raw response available via popover on the badge.
  - **Help-icon copy refreshed** across Meta / Google / TikTok / Pinterest / Microsoft credential fields. Replaced stale wall-of-text legacy instructions with one-liner + "Open setup guide" link that opens the wizard.
  - **Server-side labelling clarification pass** so client-side-only users don't see "Not set up" and think their setup is broken.
  - Files: 6 new handlers (`admin/handlers/handler-*-test-connection.php` for Meta/Google/TikTok/Pinterest/Microsoft) plus 10 new JS files (5 test-connection JS + 5 wizard JS), platform-agnostic helpers in `functions/unipixel-functions.php` (`unipixel_get_platform_connection_state`, `unipixel_classify_event_log_response`, `unipixel_diagnose_meta_error`, `unipixel_diagnose_tiktok_error`), all 5 platform setup pages modified, `admin/admin.php` modified for handler includes + JS enqueues, `admin/page-event-logs.php` + `admin/page-home.php` modified for badges/intros.
  - **Note:** an earlier in-flight design (14-day Log Server-side Response grace period via WP-Cron) was removed 2026-05-17 before release. WP-Cron unreliability + the principle of not changing user-configured settings invisibly. Underlying discoverability problem judged already solved by Test Connection + plain-English badges. See `.claude/projects/token-acquisition-ux.md` § Phase 5 (REMOVED).

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
| 1 | Setup wizard / onboarding flow | Onboarding | Days | **Done (subsumed by #34)** | Delivered as the per-platform Setup Wizard modals shipped under the token-acquisition-ux initiative (one wizard per platform: Meta / Google / TikTok / Pinterest / Microsoft). |
| 2 | Custom events wizard (visual element picker) | Onboarding, UX | Days | Not started | Current UI requires CSS selectors — unintuitive. Needs visual picker, templates, validation, test/preview. Stopgap docs written. |
| 3 | Event diagnostics dashboard | UX, Onboarding | Days | Not started | "Is it working?" — health screen showing last event, success/fail counts, connection status per platform. Data already in `unipixel_event_log`. |
| 4 | Microsoft WooCommerce pipeline (server-first) | Platform Coverage, Event Quality | Days | **Done (staged, v2.6.0 work)** | Full CAPI implementation. Server-side untested against live endpoint (token access not self-service). Client-side UET confirmed working. |
| 5 | Billing address fields in user_data | Event Quality | Hours | Not started | Available from WooCommerce, not currently sent. Improves match quality for Meta/TikTok. |
| 6 | external_id population | Event Quality | Hours | **Assessed, deprioritised** | Not a quality gap for guest-checkout WooCommerce. See `domain-knowledge/platform-discoveries.md` § META-002. |
| 7 | Additional platforms (Snapchat, LinkedIn) | Platform Coverage | Weeks | Not started | Pro tier candidates. Matters to agencies, not a blocker for solo store owners. Pinterest done (v2.5.2). |
| 8 | PHP validation — empty access token when server-side enabled | Onboarding, UX | Hours | **Done (subsumed by #34)** | Surfaced by Test Connection: empty-token state returns an explicit "No access token entered" message instead of silent failure. Test Connection button is also visibility-gated to require both Pixel ID + Access Token, so users can't trigger a silent failure path. |
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
| 30 | Meta test event code field | Event Quality, UX | Hours | **Partly done (subsumed by #34)** | TikTok wizard already auto-generates a `test_event_code` and tells the user to look in TikTok Test Events tab. Meta wizard mentions Test Events tab as a verification surface but doesn't yet auto-fire a test_event_code-tagged event. A user-facing "test_event_code" input field on the Meta page (so they can use their own pre-configured code) is still open if anyone wants it. Low priority now that Test Connection validates the token directly. |
| 31 | Consent popup localization (multi-language + editable) | UX, Platform Coverage | Days | Not started | Built-in consent popup is single-language only AND strings are hardcoded, so even single-language stores can't change wording. Dealbreaker for multi-region stores. Full spec: `projects/consent-popup-i18n.md` — covers admin UI, `.po/.mo` + override hybrid, security (kses + escaping), phased delivery (Phase 1 = editable English, Phase 2 = multi-language, Phase 3 = polish). Source: user feedback review. |
| 32 | Refresh "Cookie Consent & Tracking" docs article | Growth | Hours | Not started | Article at `unipixelhq.com/unipixel-docs/` was written before v2.6.4 + v2.6.5 work. Now significantly understates capabilities — needs to cover the 18-language popup, per-language editable wording, 5 layout styles, optional non-blocking mode, and Reject all toggle. Apply voice rules from `marketing-knowledge/writing-style.md`. Help-icon popovers in the plugin admin point at this URL, so it's high-traffic. |
| 33 | GitHub presence at `github.com/unipixelhq` | Growth | Hours | **Done (live, listing index pending flag clear)** | Public information surface for UniPixel. Profile, full long-form README, About sidebar with 20 topics, social preview image, 5 releases (v2.5.3, v2.6.0, v2.6.4, v2.6.5, v2.6.6). Account-level flag from launch-day automation velocity is currently restricting the `/releases` listing index page; data is intact, will surface once flag clears. Full context, decisions, and per-release maintenance flow: `projects/github-info-repo.md`. |
| 34 | Cross-platform token-acquisition UX | Onboarding, UX | Days–Weeks | **Done (staged, 2026-05-13)** | **Strategic theme — DONE.** All 8 phases shipped. Full record + per-phase detail in `projects/token-acquisition-ux.md` (Status: Complete). Subsumed #1 (setup wizard delivered as part of this), #8 (empty-token silent-fail handled by Test Connection's empty-token state), #30 (TikTok and Meta both now wired with test-event paths via the wizard). Origin: Moisés (nuespacios.com) support feedback 2026-05-12. |

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
| 32 | Refresh Cookie Consent docs article | Hours | Not started |
| 33 | GitHub presence (`github.com/unipixelhq`) | Hours | **Done (live, listing pending flag clear)** |

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

- **v2.6.9** (staged 2026-06-15, pending Rohan's SVN commit) — Token-acquisition UX initiative shipped (per-platform setup wizards, Test Connection buttons with platform-specific diagnostics, three-state status strips, home dashboard badges, plain-English Stored Event Logs response labels, client-side/server-side setup-page split, credential-field autofill hardening). Plus the `wp_unipixel_event_log.log_time` index (originally the 2.6.8 hotfix) and three fixes from the 2026-06-15 release-readiness test pass: (1) **log prune was completely inert** — `cleanup_logs()` passed the ID array as a single `wpdb::prepare()` arg, so the DELETE collapsed to an empty query and the event log grew unbounded; fixed with arg spread, count gauge now mirrors the true row count, thresholds retuned to 20k trigger / 10k delete. (2) **platform-settings save JSON error** — `handler-platform-settings.php` read `$_POST['pageview_send_serverside']` unguarded (the JS never sends it), emitting an Undefined-array-key warning that corrupted the AJAX JSON on hosts with `display_errors` on (the reported "Ajax request failed" / JSON.parse bug). (3) **silent PageView clobber** — same missing field meant every setup-page save rewrote `pageview_send_serverside` to 0, undoing the events-page toggle; now only written when posted. Also softened Google's connection badge to "Format checks passed" (MP validation can't confirm the API secret). Note from testing: the `log_time` index is largely cosmetic for the prune itself (MySQL ignores it at `LIMIT 10000`); the working prune is the real fix. Per-version user-facing detail: `readme.txt` changelog.
- **v2.6.7** (2026-05-03) — Terminology pass renaming "Custom events" → "Site events" across admin (general-settings, home page Event Manager card, conversion-groups UI, notice strings). Help icons added to the Event Manager (send-client / send-server / log-response). Plus two fixes: defensive guard in `unipixel-enqueue.php` around `array_merge` of plugin options (prevents front-end fatal if `unipixel_logging_options` or `unipixel_consent_settings` ever holds a non-array value), and brand-correct platform label map in `admin-wpmenu.php` (TikTok casing). Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.6** (2026-04-30) — Centralised Event Manager release. New top-level admin page for cross-platform conversion creation: pick a conceptual event (Lead, Newsletter Signup, etc.) and UniPixel fills in each platform's standard event name automatically. New URL-based trigger for custom events (fire on thank-you pages, lead pages, post-checkout pages with wildcard URL patterns). Standard event name dropdowns when defining custom events per platform. Page/URL picker reusable component. Fire-once-per-session guard for URL events. G-001 mutex enforced inline (Google client OR server, not both, except Purchase). Schema: new `unipixel_conversion_groups` table + `conversion_group_id` link column on `unipixel_events_settings`. Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.5** — popup style options (5 layouts: centred / top bar / bottom bar / bottom-left / bottom-right corner card), optional Reject all button (off by default, translated into all 18 locales), CookieAdmin (Softaculous) third-party CMP support, mobile-responsive popup (buttons stack on phones), popup animation centering fix, auto cache-bust for popup assets via filemtime suffix, plus admin polish (Test the popup section, Languages save-mode hint, dropdown label rename, plugin homepage URL update). Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.4** — multi-language consent popup (18 bundled locales + admin Languages & Content override accordion + popup language control). Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.6.0–2.6.3** — Microsoft CAPI full implementation, AddToCart fragment pixel for AJAX add-to-cart, InitiateCheckout session-based dedup, plus 2.6.1 / 2.6.3 compatibility fixes. Per-version detail: `public_html/wp-content/plugins/unipixel/readme.txt` changelog.
- **v2.5.4** — fixed 15 PHP files with U+2018/U+2019 smart quotes from v2.5.3 that had caused fatal errors on all WooCommerce events. See `domain-knowledge/platform-discoveries.md` § RQ-001.
