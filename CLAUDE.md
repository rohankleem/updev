# UniPixel plugin — CLAUDE.md

> **This is the UniPixel plugin project.** Source code: `public_html/wp-content/plugins/unipixel/`. Everything else in this repo is WordPress host scaffolding — a place to run and test the plugin locally and on `dev.unipixelhq.com`. **Do not explore WP core, other plugins, or the theme unless the task is explicitly host-level** (deploy config, DB, server). The plugin is the subject; WordPress is the venue.

This is the operating manual for working on this project with Claude. It defines how we work, where knowledge lives, and how documentation stays alive. It does not contain app or domain knowledge — those live in their own docs.

---

## Glossary

Pinned terms. Every doc, folder name, and protocol below uses these terms with these exact meanings. For product/domain vocabulary (event, pixel, clickid, CAPI, dedup, etc.) see `.claude/domain-knowledge/vocabulary.md`.

| Term | Definition |
|---|---|
| **Plugin** | The UniPixel WordPress plugin itself, at `public_html/wp-content/plugins/unipixel/`. The code we develop, obfuscate, and ship to wordpress.org. |
| **Host** | The surrounding WordPress install in this repo. Scaffolding — runs the plugin, gives us admin pages, DB, a page to hit with test events. Not the subject. |
| **Local dev site** | `https://updev.local.site` (local machine). `C:\xampp\htdocs\updev\`. |
| **Remote dev site** | `https://dev.unipixelhq.com` — first landing pad for a new plugin build. Sanity check before obfuscation. |
| **Obfuscation** | Transformation that ships to end users. `_obf/` folder runs it. Hex-encodes PHP strings, minifies JS/CSS, strips comments. Excludes `.claude/` and `CLAUDE.md`. |
| **Release** | A new version published to wordpress.org via SVN commit from the obfuscation export folder. |
| **Domain** | Tracking pixels, conversion APIs, consent, platform-specific rules. What this plugin *is about*. |
| **App** | The plugin's code, stack, architecture, dev setup. How this plugin *is built*. |
| **Project** | An initiative being worked on — a feature, investigation, or refactor. Lives in `.claude/projects/` with `Status: Active/Complete/Parked/Reference`. |
| **Session** | One working session with Claude. Starts with reading session-state, ends with updating it. |
| **Knowledge** | A fact, rule, pattern, or understanding that persists beyond a single session. If it matters next week, it's knowledge. |

---

## Session Protocol

### Start of session

1. Read `.claude/session-state-rohan.md` — what's in-flight, what's next.
2. `git fetch origin && git log --oneline -10` — check for commits since last session.
3. **Before editing plugin code**, read `.claude/app-knowledge/app-knowledge.md` — the stack, architecture, hook flow, dev workflow.
4. **Before editing tracking/platform logic**, skim `.claude/domain-knowledge/platform-discoveries.md` — known platform quirks and audit findings.
5. **Read the actual current file from disk before editing any file** — never rely on session summaries or memory. Files change between sessions.

### End of session

When the user says **"update session state"**, rewrite `session-state-rohan.md` using its rolling format. Shift "What we worked on" → "Where we came from". Write fresh "What we worked on" and "Where we need to go". No handover preamble, no timestamp footers — git history is the permanent record.

### Nature of session-state

Rolling and fresh. Only keep what's still loaded / still relevant. Git log is the permanent record — don't duplicate it. If the file grows past a screen or two, prune, don't append.

---

## Session Modes

| Mode | Trigger phrases | Claude's behaviour |
|---|---|---|
| **Build** | *(default)* | Write code, minimal doc touching. Focus on the task. |
| **Docs** | "docs mode", "let's update docs", "write this up" | No code edits unless requested. Focus on pruning, growing, restructuring knowledge files. |
| **Plan** | "plan this", "before coding...", "let's think about..." | Explore, write to `.claude/projects/`, get sign-off before code. |
| **Discovery** | "let's figure out...", "I don't know yet..." | Questions, hypotheses, ask-first. Outcome may be a new domain-knowledge entry. |

---

## Ways of Working

### Autonomy levels

| Action | Autonomy |
|---|---|
| Read files, explore plugin code, search | Just do it |
| Update `.claude/` doc files and CLAUDE.md | Propose what you're capturing, then write it. No separate permission needed — the proposal is the checkpoint. A `PreToolUse` hook in `.claude/settings.json` auto-allows `.md` edits even inside the protected `.claude/` directory (see `app-knowledge/claude-harness.md` for the mechanism). For doc-heavy sessions or testing runs, the user can launch with `claude --dangerously-skip-permissions` to skip all prompts entirely. |
| Edit plugin source code | Present approach, act on approval |
| Commit / push | Only on explicit instruction. **Commit AND push to GitHub in one step** — don't stop at a local commit. |
| Deploy to `dev.unipixelhq.com` (rsync) | Explicit — shared state |
| Release (obfuscation export + SVN commit to wordpress.org) | Explicit — ships to end users |
| Version bump | Explicit — touches three source files + release-log (see Release Gate below) |
| Delete files, destructive migrations, schema drops | Stop, warn, get explicit confirmation |

### Scope & Commits

- Only change what was explicitly asked.
- Never commit without explicit instruction.
- Present a plan before multi-file changes.
- Never delete or overwrite code without confirmation.

### Things Claude does

- Follow the session protocol above
- Use existing patterns when adding new features (mirror the trackers/, woocomm-hook-handling/, admin/ layouts)
- Verify changes work after editing — test the flow, check the logs
- Proactively herd knowledge into docs (see Knowledge Herding below)

### Things Claude does not do

- Action out-of-scope changes without approval
- Add unnecessary comments, docblocks, or type annotations to unchanged code
- Over-engineer solutions — keep it simple
- Create new files when editing existing ones would work
- **Chain compound bash commands across different permission rules.** Claude Code parses shell operators (`&&`, `||`, `;`, `|`) and permission-checks each segment separately. Default to one logical command per Bash call; only chain when all segments share an allowed pattern or the chain is truly atomic.

---

## Release Gate

Every version bump touches **four files**. Release isn't complete until all four are updated in the same change:

1. `public_html/wp-content/plugins/unipixel/unipixel.php` — `Version: X.Y.Z` header line
2. `public_html/wp-content/plugins/unipixel/unipixel.php` — `define('UNIPIXEL_VERSION', 'X.Y.Z');`
3. `public_html/wp-content/plugins/unipixel/readme.txt` — `Stable tag: X.Y.Z` + changelog entry
4. `.claude/projects/release-log.md` — move "Done Since v[X]" items into a stamped released block, update "current live version"

See `.claude/app-knowledge/deploy-and-release.md` for the full release workflow (including pre-export checks — smart quotes scan, PHP lint, etc.) that protects against recurring release-quality issues.

---

## Doc Map

```
CLAUDE.md                                    ← You are here. Operating manual.
.claude/
├── session-state-rohan.md                   ← Rohan's rolling notes. Prune, don't append.
├── settings.json                            ← Team harness settings (committed). Permission rules for .md edits.
├── settings.local.json                      ← Per-machine harness settings (gitignored).
├── sites-overview.md                        ← Cross-site reference: uphq / updev / buildio.dev / buildio.au + the github.com/unipixelhq surface.
├── updev-setup.md                           ← How updev was set up as the dev base. Reference.
├── uphq-projects-truth.md                   ← Legacy uphq facts (low relevance here — ignore unless host-level).
├── app-knowledge/                           ← How the plugin is built + how we operate it.
│   ├── app-knowledge.md                     ← Stack, architecture, hook flow, dev workflow, testing.
│   ├── deploy-and-release.md                ← rsync deploy, _obf/ workflow, version bumping, wp.org SVN.
│   └── claude-harness.md                    ← Permissions, hooks, burst mode, testing allowlist. Read when permission prompts get in the way or settings need updating.
├── domain-knowledge/                        ← What the plugin is about.
│   ├── vocabulary.md                        ← Pinned product terms (event, pixel, clickid, CAPI, dedup, etc).
│   ├── event-terminology.md                 ← Framework for event-tracking terms, ordering, copy patterns, per-platform hints. Single source of truth — read before writing any admin UI / docs / marketing copy that names an event concept.
│   ├── platform-discoveries.md              ← Cross-session audit findings, platform quirks.
│   ├── licensing-and-protection.md          ← Obfuscation + licensing strategy.
│   └── event-logs.md                        ← Stored Event Logs — user-facing guide (also informs the admin UX).
├── marketing-knowledge/                     ← How the plugin reaches users.
│   ├── positioning.md                       ← What UniPixel is, 5 pillars, industry problem + solution, language rules.
│   ├── priorities.md                        ← Where the product is now, what's blocking adoption.
│   ├── campaigns.md                         ← Active ad campaigns (Google, Meta, forums), channels, how they reinforce each other.
│   ├── unipixelhq-content.md                ← Inventory + structure of unipixelhq.com (home, docs, blog), what each surface is for.
│   ├── writing-style.md                     ← Voice rules for blog + docs articles. Apply to every published piece.
│   └── stape-alternatives.md                ← Content asset (published article).
├── projects/                                ← Per-initiative working docs.
│   ├── release-log.md                       ← Version history, backlog with buckets, unreleased work staging.
│   ├── multi-tier-clickid-persistence.md    ← Feature spec (not started).
│   ├── consent-popup-i18n.md                ← Feature spec (not started) — multi-language + editable consent popup.
│   ├── centralised-event-builder.md         ← 3-phase feature: URL trigger → event name dropdowns → conversion builder + grouping.
│   └── github-info-repo.md                  ← Public GitHub presence at `github.com/unipixelhq` — info-only repo, decisions, current state, flag situation, per-release maintenance.
└── testing/                                 ← Verification flows. Browser-agent-executable test specs.
    ├── testing.md                           ← Methodology, verification surfaces, index of all flows.
    └── flows/                               ← One file per flow (consent-grant, click-id-capture, woocommerce-purchase, etc).
```

Plugin source at `public_html/wp-content/plugins/unipixel/` has a thin `CLAUDE.md` breadcrumb but no `.claude/` folder — docs live upstream at the repo root.

### Lifecycles

| Location | Changes when... |
|---|---|
| `CLAUDE.md` | Ways of working change, doc structure changes |
| `session-state-rohan.md` | Every session |
| `app-knowledge/` | Plugin architecture, dev flow, deploy/release process, or Claude Code harness config changes |
| `domain-knowledge/` | New platform quirks, tracking rules, licensing understanding |
| `marketing-knowledge/` | Positioning shifts, active campaign tweaks, language rule changes |
| `projects/` | Initiative starts, progresses, completes. Every release bump touches `release-log.md`. |
| `testing/` | New feature → new flow file. Bug fix that slipped past tests → add a scenario. After each release, run affected flows. |

---

## Documentation Protocol

### The Decision Tree

When you learn something new, where does it go?

- **Fact about the plugin code / stack / dev flow** → `app-knowledge/app-knowledge.md`
- **Fact about deploy / release / obfuscation / version bumping** → `app-knowledge/deploy-and-release.md`
- **Claude Code harness behaviour: permissions, hooks, burst mode, testing allowlist** → `app-knowledge/claude-harness.md`
- **Platform quirk, tracking rule, consent edge case, licensing insight** → `domain-knowledge/` (pick the right file, or vocabulary.md if it's a term)
- **Naming, ordering, or copy rule for event-tracking concepts (admin UI, docs, marketing)** → `domain-knowledge/event-terminology.md`
- **Marketing positioning / pillar refinement / campaign move / competitor note** → `marketing-knowledge/` (positioning.md, campaigns.md, or priorities.md)
- **Something we're actively building** → `projects/{initiative-name}.md` (and a draft flow in `testing/flows/`)
- **A way to verify behaviour (test scenario, expected payload, baseline fixture)** → `testing/flows/{flow-name}.md`
- **Something that happened this session** → `session-state-rohan.md`
- **A rule about how Claude and the team collaborate** → `CLAUDE.md`

If you can't pick one branch in 5 seconds, the tree needs a new rule. Propose one.

### Pruning & Growing

- **New file**: when a concept doesn't fit any existing doc and needs more than 1-2 sentences.
- **Split a file**: when it's dense enough that you stop reading it start-to-finish, or one sub-topic dominates the edits. Extract, leave a pointer stub.
- **Rename a file**: when content drifted from the filename.
- **Remove stale content**: don't document what's already visible from code, git history, or the filesystem. Only document what isn't obvious — patterns, conventions, rationale, constraints.
- **Completed projects → reflect knowledge back**: when a feature ships, the knowledge it produced (patterns, conventions, platform facts) should be reflected into `app-knowledge/` or `domain-knowledge/`. The project doc stays in `projects/` as history with `Status: Complete`.
- **Projects don't get deleted**: mark `Status: Complete` when shipped.
- **Single source of truth per concept**: pillars live in `positioning.md` only; campaigns reference by name, not by restating. If you find yourself duplicating, stop and link.

### When to update docs

- Learned a new fact → add to the right file per the decision tree
- Shipped a release → version bump gate (four files) + reflect retained knowledge into `domain-knowledge/`
- A rule changed → update the file, note in `session-state-rohan.md`
- Repeated a correction → that's a signal to write it down

---

## MD vs JSON

Default to Markdown. Use JSON only when the content is genuinely structured data with a stable schema that either Claude or the plugin code benefits from parsing (lookup tables, config, picklists). Tables in MD are readable; don't JSONify them just because they're tabular. **Don't JSONify too early** — if the shape is still being discovered through conversation, keep it in MD.

---

## Knowledge Herding

Claude proactively captures knowledge. This is a core responsibility, not optional helpfulness.

### Why

Plugin knowledge currently lives in past sessions, scattered code, and Rohan's head. The goal of this documentation system is to keep it reachable from one session to the next. Knowledge decays. Sessions are ephemeral. The moment of understanding is the cheapest moment to document — later costs more and loses fidelity.

### When

- After a discussion clarifies a platform quirk, a tracking rule, or a positioning decision
- After debugging reveals a rule or constraint worth remembering
- After repeated corrections suggest a pattern worth writing down
- When Claude notices it has explained the same thing twice
- When a conversation produces vocabulary worth pinning

### How

Claude recognises the moment and says: *"I think we've reached an understanding here — let me capture this in [specific file]."* Then writes it. Settings already permit `.md` file edits without prompting — the proposal itself is the checkpoint. If the user objects or corrects, Claude adjusts.

### Where

Per the decision tree above. Always name the specific file, never just "the docs."

### Why this is documented here

So every Claude session inherits this behaviour. Without this rule, each new session starts naive and waits to be told. With it, every session actively maintains the knowledge base.
