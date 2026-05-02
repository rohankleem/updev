# GitHub presence — `unipixelhq/unipixelhq`

**Status:** Active. Live and indexable. `/releases` listing index restricted by account flag (see § Flag situation).

**Started:** 2026-05-01
**Live:** 2026-05-02
**URL:** `https://github.com/unipixelhq/unipixelhq`

---

## Why a GitHub presence

A public GitHub surface acts as a discovery, trust, and SEO play for the UniPixel brand. Reasons:

- **DA-96 backlink** to unipixelhq.com from the README and About sidebar. Same logic written up for the Facebook page in `campaigns.md` (high-authority domain pointing back to the marketing site).
- **Branded-search hygiene.** Anyone googling "unipixel github" expects a result. Without one, that's a trust gap, especially for the technical buyer.
- **GitHub Topics** are their own discovery surfaces. Topic pages like `/topics/server-side-tracking` and `/topics/woocommerce` index repos that claim them.
- **Awesome-list eligibility.** Curated lists like "awesome-wordpress-plugins" only link to repos. Can't be on them without one.
- **Indexable release pages.** Each release becomes a separately-indexed page Google can rank.
- **Public Issue tracker** (when used) generates user-question long-tail SEO content.
- **Trust signal for technical buyers.** Server-side tracking buyers skew dev-flavoured. They check.

---

## What it is, what it isn't

**Is:** a public-facing information surface. README with the full marketing positioning, comparison table, full changelog, links into wp.org install and unipixelhq.com docs. Releases mirror wp.org versions. Topics target search.

**Isn't:** the source of truth for plugin code. The plugin ships through wp.org SVN. This GitHub repo holds no plugin source, obfuscated or otherwise.

The "no source code" decision was deliberate. The licensing/protection strategy in `domain-knowledge/licensing-and-protection.md` relies on obfuscation. Public unobfuscated source would undermine that. A docs/issues-only repo captures most of the discovery and trust value at zero risk.

---

## Architecture decisions

| Decision | Choice | Why |
|---|---|---|
| User account vs organisation | **User account** named `unipixelhq` | Org would require a separate admin user whose name appears on commits. User account means namespace and committer share one identity. Simpler. Convertable to org later if collaborators are ever needed. |
| Repo name | `unipixelhq/unipixelhq` (single repo) | GitHub renders a same-name-as-username repo's README on the profile page. One repo serves both as profile signpost and product page. A two-repo plan was originally proposed (`unipixelhq/.github` for profile + `unipixelhq/unipixel` for project) but rejected: two pages on the same brand cannibalise each other for the same query, and exact-match URL slugs stopped being a real ranking factor years ago. |
| Source code | **No.** Documentation and issues only | Preserves the obfuscation-based licensing strategy. README states "ships via wp.org" up front so visitors don't expect code. |
| Wikis / Projects / Discussions | **Off.** Issues only | Reduces surface area. Issues are the only way for users to interact and the indexable long-tail surface. |

---

## Current state

### Live
- Profile: avatar (UniPixel logo), bio, URL `https://unipixelhq.com`, location `Australia`, Facebook social link
- Repo public, GPLv2
- README: positioning, why server-side, why WordPress changes the picture, supported platforms table, what's in the box, comparison table, install pointer, docs pointer, support, comparisons, changelog with full version history (v1.1.1 through v2.6.6), license, closing CTA. Voice rules from `marketing-knowledge/writing-style.md` applied throughout.
- About sidebar: SEO-dense description (345 chars), website link, 20 topics targeting all major search variants (`wordpress-plugin`, `woocommerce`, `server-side-tracking`, `meta-conversions-api`, `facebook-pixel`, `google-analytics-4`, `tiktok-events-api`, `pinterest-conversions-api`, `microsoft-uet`, etc.)
- Social preview image (1280×640) uploaded
- 5 releases on 5 unique commits: v2.5.3, v2.6.0, v2.6.4, v2.6.5, v2.6.6 (Latest). Each release page renders independently and is indexable.

### Restricted (account flag, see § Flag situation)
- `/releases` listing index page renders empty
- `/releases.atom` returns 404
- `api.github.com/repos/unipixelhq/unipixelhq/...` endpoints return 404
- Code search disabled with a public banner

These are functional gaps but not visibility gaps for search engines. Each release page, the repo home, the Tags page, and individual release URLs all render fine to public visitors and to Google.

### Pending (do once flag clears)
- Clean up 5 stray HTML comment lines from the README (`<!-- release: vX.Y.Z -->` added during the per-release commits to give each release a unique target). Single small commit.
- Three pinned issues: Roadmap, Known limitations, Where to get help. Each one a long-lived indexable page giving the Issues tab structure.
- Issue templates at `.github/ISSUE_TEMPLATE/*.yml` for the five platforms (Meta, Google, TikTok, Pinterest, Microsoft) plus generic bug and feature request.

---

## Flag situation

The `unipixelhq` account was flagged by GitHub's anti-abuse system on 2026-05-02. Triggered by high-velocity automated activity in the first 24 hours: avatar upload, profile setup, repo creation, big README paste, About sidebar config with 20 topics, 5 releases created in rapid sequence, 5 releases deleted, 5 tags deleted, README edits, 5 fresh releases. All within hours, all via browser automation through Claude in Chrome.

**Public symptoms:** code search restricted with a banner, `/releases` listing returns empty, `/releases.atom` 404s, REST API returns 404 for the repo. Direct release URLs and the repo home work fine.

**Recovery path** (not yet executed as of 2026-05-02):
1. Verify the email address on the `unipixelhq` account if not already verified
2. Appeal at https://support.github.com/contact?subject=account-flagged. Tell them it is a brand account for a WordPress plugin distributed via wordpress.org.

Typical clearance for legitimate accounts: a few days. The data is intact and will start surfacing on the listing pages once the flag is lifted; no action needed at that point.

**Lesson, applies to any future GitHub account creation:** do not drive high-velocity automated activity on a fresh GitHub account in the first 24 hours. Either pace it manually with human delays, or restrict automation to read-only operations until the account is established. Applies to any future Buildio or related accounts.

---

## How it fits the marketing knowledge base

- **`campaigns.md` § Growth channels:** added as Channel 7 (GitHub presence).
- **`sites-overview.md`:** added as a public brand surface alongside the four web properties.
- **`unipixelhq-content.md`:** noted as a complementary surface to the marketing site (different role: marketing site is the conversion destination, GitHub is a discovery and trust hub that links into wp.org and unipixelhq.com).
- **Out of scope for plugin source.** The plugin codebase at `public_html/wp-content/plugins/unipixel/` does not reference or depend on this repo in any way.

---

## Per-release maintenance

Going forward, every wp.org release should also be tagged on GitHub the same day:

1. After publishing to wp.org via SVN, go to `github.com/unipixelhq/unipixelhq/releases/new`.
2. Tag matches the wp.org version (`v2.6.7` etc).
3. Title format: `vX.Y.Z: <short headline summarising the release>`.
4. Body: lift the bulleted "What's new" from `readme.txt` § Changelog for that version. Trim for voice rules in `writing-style.md` (no em dashes, comparative not absolute, specific numbers).
5. Leave **Set as latest release** ticked for the new version.
6. After publishing, update the README's `## Changelog` section with the new entry (single small commit).

About 5 minutes per release. The body content pattern is established by the v2.5.3 → v2.6.6 entries.

**README content drift:** the long-form positioning, comparison table, and "what's in the box" sections in the README should track major positioning shifts in `positioning.md`. Update when `positioning.md` changes meaningfully.

**Topics:** stable. Update only if a new platform is added or a search term shifts significantly.

---

## Open questions / future work

- **Stars bootstrap.** The repo has 0 stars at signoff. Worth a manual "star from a personal account" once the user logs in elsewhere, just to seed the count above zero.
- **Awesome-list submissions.** Once the repo has been live for a couple of weeks (so it doesn't look brand-new), submit PRs to `awesome-wordpress-plugins`, `awesome-marketing-tools`, and similar curated lists. Each submission is a real backlink.
- **Convert to org if collaborators are added.** GitHub supports a one-step user-to-org conversion. Username preserved, repos preserved. Only do this if there is a real reason (a contractor, a triage helper).
