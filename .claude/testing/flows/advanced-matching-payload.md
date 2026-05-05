# Flow: Advanced Matching payload

**Status:** Draft
**Last run:** —
**Covers:** The `advanced_matching_enabled` toggle in General Settings. When on, WC purchase and checkout events include hashed user data (email, phone, name, address) in the CAPI payload. Verifies the user_data block is present, hashed correctly (lowercase trim then SHA-256), and has the expected fields.

Silent regression risk is high here: if Advanced Matching breaks, events still fire and rows still log, but match quality on the platforms drops without any visible error in the plugin admin. This flow is the only thing that catches that.

## Setup

- WooCommerce store with at least one purchasable product
- A test customer account with realistic field values (email, phone, first name, last name, billing address). Anchor the test on consistent fixture values so SHA-256 hashes are deterministic and diffable across runs.
- Suggested fixture: `test+matching@buildio.dev`, `+61400000000`, `Test`, `Buyer`, `123 Test St, Sydney, NSW, 2000, AU`
- Meta has valid pixel ID + CAPI token (Meta has the most documented user_data block; same principle applies to TikTok and Pinterest, run those as additional scenarios if time allows)
- Meta Purchase row: `send_server = 1, send_server_log_response = 1`
- DB store toggles on for purchase events
- Baseline: per `baseline-state.md`

## Scenario 1: `advanced_matching_enabled = 1` adds hashed user_data to Meta Purchase

**State delta from baseline:**
- General Settings: `advanced_matching_enabled = 1`
- WooCommerce: customer cart has the fixture customer data ready to be entered at checkout

**Action:** Complete a WooCommerce purchase as the fixture customer (entering the fixture email, phone, name, billing address at checkout).

**Asserts:**
- The Meta Purchase row in `wp_unipixel_event_log` has `sent_data` containing a `user_data` block
- The `user_data` block has all expected hashed fields: `em` (email), `ph` (phone), `fn` (first name), `ln` (last name), `ct` (city), `st` (state), `zp` (postal), `country`
- Each hash value is **64 hex characters** (SHA-256 output) and lowercase
- Each hash is the SHA-256 of the lowercase trimmed input. Verify by re-hashing the known fixture inputs in the test runner and matching: e.g. `sha256('test+matching@buildio.dev')` should equal the `em` value. **This is the load-bearing assert.**
- No raw (unhashed) PII appears anywhere in the payload (sanity: search the full `sent_data` blob for the literal email string and confirm zero matches)

**Captures:**
- The full Meta Purchase `sent_data` payload → `expected/scenario-1-meta-purchase-payload.json` (with the user_data block stable across runs given the fixture customer)

---

## Scenario 2: `advanced_matching_enabled = 0` omits user_data block

**State delta from baseline:**
- General Settings: `advanced_matching_enabled = 0`

**Action:** Complete a WooCommerce purchase as the fixture customer.

**Asserts:**
- The Meta Purchase row in `wp_unipixel_event_log` has `sent_data` with **no `user_data` block**, OR the block contains only fields that don't depend on Advanced Matching (e.g. `client_user_agent`, `client_ip_address` may still appear; verify on first run which fields are gated by the toggle and which aren't)
- Specifically: `em`, `ph`, `fn`, `ln`, `ct`, `st`, `zp` should be absent
- The Purchase event still fires successfully (the row exists, `response_message` shows Meta accepted it). Match quality is reduced without Advanced Matching but the event itself isn't suppressed.

**Captures:**
- The full Meta Purchase `sent_data` payload → `expected/scenario-2-meta-purchase-payload-no-am.json`

---

## Scenario 3: Hashing format is correct

Specifically tests the hashing contract independent of the toggle on/off. The contract per Meta's docs: lowercase the input, trim whitespace, then SHA-256. UniPixel must follow this exactly or Meta won't match the hash to its known users.

**State delta from baseline:**
- General Settings: `advanced_matching_enabled = 1`
- Use a fixture customer with edge-case-shaped values:
  - Email with mixed case and surrounding spaces: `  Test+Matching@BuildIO.DEV  `
  - Phone with formatting: `+61 400 000 000` (the trim-and-normalise behaviour is what we're testing)
  - First name with leading capital and trailing space: `Test ` → expected to hash as `test`

**Action:** Complete a WooCommerce purchase with this fixture.

**Asserts:**
- `em` hash matches `sha256('test+matching@buildio.dev')` (lowercased, trimmed)
- `ph` hash matches `sha256('+61400000000')` (whitespace removed, no other normalisation unless documented)
- `fn` hash matches `sha256('test')` (lowercased, trimmed)
- All other hashed fields follow the same lowercase-trim-then-hash rule

**Captures:**
- The full Meta Purchase payload → `expected/scenario-3-edge-case-hashes.json`
- The expected hashes computed from the inputs → noted in the run log for the asserts

---

## Notes for the runner

- This flow as written is Meta-scoped because Meta has the most documented and stable Advanced Matching contract. TikTok and Pinterest have similar fields but slightly different naming conventions. Adding scenarios for TikTok (`email`, `phone_number` in their user_data) and Pinterest (`em`, `ph`, etc. but slightly different field set) is a follow-up.
- Microsoft UET CAPI's user data shape is documented separately if/when added; defer until that platform's match-quality spec is captured in `domain-knowledge/`.
- The fixture customer data must be consistent across runs for the captures to be diff-able. If the fixture changes, all baselines need re-blessing.
- This flow does not test the wp.org plugin directory's Advanced Matching docs article — that's a content task, not a code task.
