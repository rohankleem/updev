# Licensing, Gating & Obfuscation

Protection strategy for UniPixel — what we do today, what we've considered, what we plan to build when download traction justifies it.

## Current Obfuscation (`_obf/`)

- Hex-encodes PHP strings, minifies JS/CSS, strips comments
- Good deterrent against casual copy-paste theft
- **Not a security boundary** — AI tools can reverse all of it in minutes

## Why Obfuscation Alone Fails for Licensing

- AI collapses the effort to find and patch license checks
- Any function that returns a license validity boolean can be patched to return `true`
- This applies even if the check phones home — the *response handling* is local code
- `--rename-identifiers` flag helps but is risky and still beatable

## Approaches Considered & Rejected

### Server-gated features
- Core actions must run from the user's WordPress install, not a relay server
- Not viable for UniPixel

### Periodic heartbeat / file thumbprinting
- Plugin phones home on a schedule with domain, license key, file hashes
- Server flags silence or tampered files
- Adds complexity, still hackable — someone patches out the heartbeat and accepts no updates
- Could complement the primary strategy but not worth building standalone

### Free core + premium add-on (two plugins)
- Standard model (ACF, WPForms pattern): free on WordPress.org, premium downloaded separately
- Strongest protection — premium code never publicly available
- **Rejected** — maintaining two plugins is not feasible at current scale

## Primary Strategy: License-Gated Updates (Single Plugin)

The simplest and most effective approach for UniPixel:

- **One plugin, one codebase** — no split to maintain
- **Updates require a valid license key** verified against a licensing server
- No valid license = no new versions = plugin falls behind and breaks as platforms change their APIs
- For a tracking plugin this is especially effective — Meta, Google, TikTok API changes make outdated versions useless
- **Obfuscation remains layer 1** — deters casual theft of any given version
- **Licensing SDK (e.g. Freemius, EDD Software Licensing)** handles validation, update delivery, and payment — no need to build the licensing server from scratch

### Why this works

- Pirates get one frozen version that degrades over time
- No cat-and-mouse with heartbeats or local license checks to hack
- The protection mechanism (update server) is entirely under our control
- Proven model used across the WordPress ecosystem

## Registration & Onboarding Flow (Planned)

### Plugin-side registration
- On first install / activation, plugin prompts user to register
- Collects: production domain + optional testing/staging domain
- Creates an "instance" on the licensing server — status: active
- No registration = plugin still works but with soft limits (see below)

### Admin dashboard (server-side)
- See all registered installs: domain, status (active/deactivated), version, last seen
- Ability to deactivate a license remotely
- Monitor usage / call volume across the install base
- Potential to surface live value to registered users (usage stats, health checks, etc.)

### Unregistered / expired behaviour
- Visible "UNREGISTERED PLUGIN" or "REGISTRATION EXPIRED" notice in WP admin
- Not hidden — intentionally embarrassing for client-facing agency installs
- Plugin continues to function but with soft limits, nudging toward registration

### Soft limits (freemium gating)
- Options being considered (not finalised — depends on download volume):
  - **Time-limited free tier** — e.g. 3 months full access, then registration required
  - **Call/event volume cap** — e.g. X server-side events per month before requiring registration
  - **Platform cap** — e.g. one platform free, additional platforms require registration
- The right model depends on traction — need to reach 1000s of downloads before a commercial licensing strategy makes sense
- Until then, keep it free to maximise adoption and feedback

## Commercial Viability Note

Licensing and gating are future concerns. Current priority is distribution, adoption, and proving the plugin's value. The registration/dashboard infrastructure should be designed but not necessarily built until download numbers justify it. All the above is documented for when the time comes.

## Bottom Line

- Obfuscation = **layer 1** — stops zero-effort theft, worth keeping
- License-gated updates = **layer 2** — the primary protection mechanism
- No local-only approach is fully secure against a motivated actor with AI
- Protection is about raising the cost, not making it impossible
