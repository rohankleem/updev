# Public frontend endpoint hardening

**Status:** Not started (Improvement / follow-up)
**Origin:** Static security review of v2.6.11 (forum report, 2026-09-16). The three critical/high items from that review were fixed immediately (see "Applied already" below). This doc captures the two remaining items that are **not** clean drop-ins and need a design decision.

---

## Applied already (not part of this doc's scope)

Fixed in the same session the review landed — reflected here only for context:

- **Token disclosure (critical):** `unipixel_handle_send_event_result()` now redacts credentials from the `args` blob before it is echoed or logged (`unipixel_redact_credentials_from_args()` in `functions/send-server-event-handle-result.php`).
- **Broken access control (high):** `manage_options` checks added to the 7 settings AJAX handlers that only checked a nonce; the two ungated `admin_enqueue_scripts` callbacks in `admin/admin.php` are now page-gated so `unipixel_ajax_nonce` is no longer printed on every wp-admin page.
- **Payload size cap (partial Claim 3):** `ajax-handle-log-client-event.php` rejects `json_data_sent` over 20 KB.

---

## Item A — Cache-safe frontend nonces (review item #4)

### Problem

Frontend AJAX nonces are embedded in page HTML via `wp_localize_script`:

- `unipixel_log_client_event_nonce` — `functions/unipixel-enqueue.php` (`UniPixelAjax.nonce`)
- `unipixel_track_nonce` — the per-platform `trackers/*-enqueue.php` files (`UniPixelEventData<Platform>.nonce`)

Consumed by `functions/ajax-handle-log-client-event.php` and the `trackers/*-ajax-listener-send-server.php` + `trackers/microsoft-handler.php` listeners via `check_ajax_referer`.

WordPress nonces live for one nonce-tick pair (~12–24h). When a full-page cache (WP Rocket, W3TC, LiteSpeed, host cache) serves cached HTML older than that, the embedded nonce is already expired → `check_ajax_referer` fails → **client-first tracking and server-event relays silently fail** until the cache is purged/regenerated. Most production WooCommerce sites run a page cache, so this is a real, hard-to-diagnose reliability loss.

### Key reframing (why this is now easy to change)

After the token-leak fix, these public `nopriv` endpoints perform **no privileged or sensitive action** for anonymous users. For logged-out visitors the nonce is identical for everyone and sits in public HTML anyway, so it was never a meaningful CSRF/authorization control here — just a weak "same-site" signal. That means we can relax or replace it without weakening security.

### Options

1. **Relax / drop the nonce on the public endpoints.** Simplest, fully cache-immune. Tradeoff: endpoints become openly callable → makes Item B (rate limiting) more important. Recommended *in combination with* Item B.
2. **Runtime fresh-nonce fetch.** On page load, one small **uncached** admin-ajax/REST call returns a fresh nonce before any event fires. Cache-immune and keeps a nonce. Tradeoff: +1 request per page; the bootstrap endpoint itself must be excluded from cache.
3. **Cache-plugin-friendly handling / documentation only.** Document a purge cadence or integrate with known caches. Weakest; not recommended.

### Recommendation

Option 1 + Item B, or Option 2 if we want to keep a token on the wire. **Decision needed from Rohan.**

---

## Item B — Rate limiting / replay / dedup (remainder of review item #3)

The payload-size cap shipped. Still missing on the public endpoints (`unipixel_log_client_event`, `ajax_data_for_server_event_*`, `unipixel_ajax_server_event_microsoft`):

- **Rate limiting** — no throttle; an attacker can flood the log table (churn/prune thrash) or force repeated outbound CAPI calls (ad-account data pollution, API-quota burn).
- **Replay protection** — a captured request replays freely within the nonce window.
- **Event-ID dedup** — none locally; the plugin relies on the ad platforms to dedup by `event_id`.

### Why deferred (not a clean drop-in)

An IP-keyed transient throttle can drop legitimate bursts (shared NAT, fast browsing, bots that are real customers) and **silently lose events** — for a tracking plugin that's its own class of bug. Needs a tested threshold + a considered key (IP? IP+UA? WC session?) rather than a bundled quick fix.

### Sketch

- Per-key counter in a transient (e.g. `unipixel_rl_<hash>`), short window (e.g. 60s), generous cap.
- Fail open on storage errors (never block real tracking because the cache backend hiccuped).
- Consider a short-TTL `event_id` seen-set for local dedup if we stop trusting platform-side dedup.

---

## Coupling

Items A and B are linked: relaxing the nonce (A/option 1) raises the value of B. Decide them together.

## Testing

Add a `testing/flows/` scenario that simulates a stale/absent nonce and confirms client-first + server relays still succeed (guards against regressions once A lands).
