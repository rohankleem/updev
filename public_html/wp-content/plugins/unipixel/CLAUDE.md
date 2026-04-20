# UniPixel plugin — source directory

You're in the plugin code. The operating manual and all knowledge live at the **repo root**.

- Operating manual: `/CLAUDE.md`
- Knowledge:
  - `/.claude/app-knowledge/` — stack, architecture, hook flow, dev workflow, deploy/release
  - `/.claude/domain-knowledge/` — vocabulary, platform-discoveries, licensing-and-protection, event-logs
  - `/.claude/marketing-knowledge/` — positioning, priorities, campaigns
  - `/.claude/projects/` — release-log (backlog + version history), in-flight features

**Do not add `.claude/` or CLAUDE.md content at this level** — docs live upstream. This breadcrumb exists only so a session that starts `cd`'d into the plugin folder knows where to find them.

## Plugin-local conventions

- Version bumps touch **four files** — see repo root CLAUDE.md § Release Gate, and `/.claude/app-knowledge/deploy-and-release.md` for the full workflow (including the pre-export checklist that prevents recurring release-quality issues like smart-quote contamination).
- `.claude/` and `CLAUDE.md` are excluded from obfuscation output (`_obf/exclude-list.txt`) — they never ship to end users.
