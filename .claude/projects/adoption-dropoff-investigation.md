# Adoption drop-off investigation

**Status:** Active
**Started:** 2026-10-15
**Trigger:** Rohan reports downloads and activations down, deactivations up. Same day as the cPGuard activation-fatal thread (RQ-003).

## Data sources and their limits

| Source | What it shows | Limit |
|---|---|---|
| wp.org downloads API (`api.wordpress.org/stats/plugin/1.0/downloads.php?slug=unipixel&limit=365`) | Daily downloads | Mostly update waves; each release adds ~180-270 downloads in a week (roughly the install base). Read the quiet-day baseline (days more than 6 days after a release), not monthly totals. |
| wp.org plugin info API | Active installs (bucketed, 200+), rating, versions | No public active-install history (endpoint 404s). |
| buildio.dev telemetry (`unipixel_metric_log()` -> `buildio.dev/wp-json/unipixelmetrics/v1/log`) | Activate / Deactivate / Uninstall / Plugin Update / settings events per site | **Lossy.** See below. Live data only on buildio.dev; the local `wx4gk_unipixelmetrics_logs` copy in the updev DB ends 2026-03-21. |

**Telemetry delivery:** `timeout => 0.01, blocking => false`, but WP's Requests cURL transport floors the timeout to 1s (`Requests/src/Transport/Curl.php`, "cURL requires a minimum timeout of 1 second"). Each event gets about 1s to complete DNS + TCP + TLS + send. From the dev laptop (AU) to buildio.dev (66.45.248.176) the TLS handshake alone took 0.5-1.1s and a full response 2-4s. So delivery depends on host geography and buildio.dev's latency: counts can move without any real change in installs. Compare ratios (deactivate/activate) rather than absolute counts, and check whether buildio.dev's hosting/DNS changed around any apparent drop.

## Findings (2026-10-15)

### wp.org downloads are not down
Quiet-day baseline: ~3-4/day Dec-Feb, ~10/day May-mid Sep, **13-14/day since 2.6.12 (Sep 16)**. Low months (Aug 2026: 316) are months without a release. The 2.6.12 update wave (~208 above baseline) matched 2.6.10's (~211), so the auto-updating install base hadn't shrunk by mid-September. Version share: 2.6 = 89%, 2.3 = 11% (sites frozen since before Feb 24).

### Telemetry baseline (local copy, Feb 2025 to Mar 21 2026, test/own sites excluded)
- 483 sites activated; 78% saved at least one setting.
- 54% deactivated at some point: **26% within an hour of first activation**, 34% within a day.
- 199 of 259 deactivators had configured something first. Pattern: set it up, can't see it working, leave.
- Deactivate/activate ratio rose from ~20% (Apr-Jun 2025) to 50-95% (Sep 2025 to Feb 2026).
- Needs a fresh export of the live table to say anything about Sep-Oct 2026. Rerun `funnel.php` (see Test tooling) against it.

### Activation (fresh WP 6.9.4 / PHP 8.3 / MySQL 8.4, throwaway DB `updev_fresh`)
- Shipped 2.6.12 build and source both activate cleanly on a fresh site, with and without WooCommerce: 6 tables, defaults written, no unexpected output, all 12 admin pages render.
- Only noise: two `Undefined array key enableGoogleDebugView{Client,Server}Side` warnings on General Settings in the fresh state (`page-general-settings.php`, logging options created without those keys).
- Quarantined `trackers/meta-enqueue.php` reproduces the forum fatal exactly (RQ-003).
- PHP 7.x fatals on activation (RQ-004). wp.org stats: PHP 7.0-7.4 is ~20% of WordPress sites.
- **Not yet tested on WP 7.0 / 7.1.** Local is 6.9.4. WP 7.0 shipped 2026-05-20, 7.1 on 2026-08-19 (~70% of sites). "Tested up to: 6.9" means Plugins > Add New shows "Untested with your version of WordPress" to those sites.

### Front end (updev, headless Chrome over CDP, consent banner on)
- Consent gating works for Meta and Pinterest: nothing fires before Accept; after Accept, Meta PageView beacon, Pinterest pagevisit, Pinterest server-side PageView and client-event logs all succeed.
- **WP-002 (theory, unconfirmed):** an expired page token (>12-24h) is rejected with `403 -1`. No evidence any real UniPixel site serves cached pages that old. Raised in a forum thread on 2026-09-08.
- **PIN-002:** Pinterest `event_id` never reaches Pinterest (passed in the callback slot), so browser/CAPI dedup can't work.
- **G-003:** GA4 cookies set before consent; no Consent Mode signals.

### What deactivating sites did first (telemetry, Feb 2025 to Mar 21 2026, 266 sites, test sites excluded)

| Before their first deactivation | Sites | Left within 10 min | Within 1h | Later |
|---|---|---|---|---|
| Never saved any platform settings | 88 (33%) | 44 | 22 | 22 |
| Saved settings, never enabled a platform | 1 | 0 | 0 | 1 |
| Enabled a platform (server-side state not logged before 2.6) | 172 (65%) | 26 | 38 | 108 |
| Enabled a platform, server-side on (2.5.4+) | 5 | 1 | 0 | 4 |

Telemetry does not record whether events actually reached the platforms, so it cannot say why the configured two-thirds left. Ruled out as drivers: "settings saved but platform off" (1 site), Meta Graph API version (v14.0 still answers like v23.0), PHP 7 (constant since 2.1.0 and invisible to telemetry). The stale-nonce gate (WP-002) has been in every release since 2.1.0, so it is a standing failure for cached sites, not a recent change. Explaining the recent rise needs the live table from buildio.dev.

## Ranked hypotheses for drop-off

1. **(Unconfirmed theory) Server-side fails on cached sites** (WP-002). No evidence it happens on real sites; was wrongly ranked first.
2. **Activation fails outright** on PHP 7.x (~20% of sites) and on hosts whose scanners quarantine hex-encoded files. These never show up in telemetry (activation hook never runs).
3. **Trust signals at the point of install:** "Untested with your version" for ~70% of sites, hex-obfuscated code (forum threads Mar 9, Mar 18, Oct 14), unresolved public security thread.
4. **Measurement artefact:** lossy telemetry (above).

## Next steps
- Get a fresh export of the live `unipixelmetrics_logs` and rerun the funnel for Jun-Oct 2026.
- Test on WP 7.1, then bump "Tested up to".
- Fix WP-002, PIN-002, G-003, RQ-004 (Requires PHP 8.0), RQ-003 (drop `--encode-strings`).

## Test tooling (session scratchpad, 2026-10-15; move into repo if kept)
- `harness.php`: fresh-install activation harness. Runs WP from updev against `updev_fresh` with any table prefix and plugin dir (`WP_PLUGIN_DIR` override, Dotenv is immutable so pre-set `DB_NAME`/`TABLE_PREFIX` win). Steps: createdb, install, activate, admin, deactivate, state. Blocks all outbound HTTP so test activations never reach the live telemetry.
- `cdp-smoke.mjs`: Node 22 + headless Chrome over DevTools protocol. Loads a URL, captures console/exceptions and pixel + admin-ajax traffic, optionally clicks consent Accept and reloads. Needs a normal UA override: Meta's pixel ignores HeadlessChrome.
- `nonce.php`: builds a logged-out nonce N ticks old, for stale-cache tests.
- `funnel.php`: telemetry funnel (per-month counts, time to first deactivation).
- The in-app browser pane blocks every subresource on `updev.local.site` (`ERR_BLOCKED_BY_CLIENT`), so it can't run the front end. Use the CDP script or Claude in Chrome.
