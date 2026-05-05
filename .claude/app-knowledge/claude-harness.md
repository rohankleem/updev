# Claude Code Harness — permissions, hooks, burst modes

How this project is configured to make Claude work efficiently inside it. Sibling to `deploy-and-release.md` — both are "how we operate", not "how the plugin code is structured."

When permission prompts get in the way, this is the doc to update. When a session hits a permission wall and isn't sure why, read this first.

---

## Why permission prompts are special in `.claude/`

Claude Code hardcodes `.claude/` as a "protected directory." Edits to files inside it trigger a "sensitive file" prompt that **bypasses normal `permissions.allow` rules**. The hardcoded check runs before permission rule evaluation, so even a perfectly-formed `Edit(.claude/**/*.md)` rule won't suppress the prompt.

Exempt subdirectories (where Claude routinely creates content):
- `.claude/commands/`
- `.claude/agents/`
- `.claude/skills/`
- `.claude/worktrees/`

Everything else under `.claude/` (our knowledge folders, projects, testing flows, session-state) is in the protected zone.

References:
- [Configure permissions — Claude Code docs](https://code.claude.com/docs/en/permissions)
- [GitHub issue #37765](https://github.com/anthropics/claude-code/issues/37765) — bypassPermissions does not bypass `.claude/`
- [GitHub issue #37253](https://github.com/anthropics/claude-code/issues/37253) — bypassPermissions still prompts for `~/.claude/` files

---

## What actually works (status 2026-05-03)

**Reliable**: launch Claude Code with `claude --dangerously-skip-permissions`. This bypasses the hardcoded `.claude/` sensitive-file check. First launch shows a one-shot acknowledgement dialog, then silent for that session and every subsequent session (remembered via `skipDangerousModePermissionPrompt: true` in user settings).

**Unreliable on this machine**: the `PreToolUse` hook approach below was previously claimed to short-circuit the prompt by returning `permissionDecision: "allow"`. In practice it does not always fire on Windows — repeated `.claude/*.md` edits still trigger the sensitive-file prompt even with bypass mode active and the hook configured. Suspected cause: the hook's `echo '{...}'` command uses bash-style single-quoting; if Claude Code on Windows runs hook commands via cmd / PowerShell, the single quotes are not stripped, the JSON is malformed, and the hook decision is silently discarded. Not yet verified. If revisiting: rewrite the hook command to use a `.bat` or `.ps1` script that outputs JSON without quote-stripping ambiguity, or use stdin-piped JSON.

**Permission `allow` rules alone do not work for `.claude/` paths.** The hardcoded check runs before permission rule evaluation.

Live config (in [.claude/settings.json](.claude/settings.json)):

```json
{
  "permissions": {
    "allow": [
      "Edit(**/*.md)",
      "Write(**/*.md)",
      "MultiEdit(**/*.md)"
    ]
  },
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Edit",
        "hooks": [{
          "type": "command",
          "if": "Edit(**/*.md)",
          "command": "echo '{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"permissionDecision\":\"allow\",\"permissionDecisionReason\":\"md-auto-allow per project policy\"}}'"
        }]
      },
      { "matcher": "Write",     "hooks": [/* same shape, if: Write(**/*.md) */] },
      { "matcher": "MultiEdit", "hooks": [/* same shape, if: MultiEdit(**/*.md) */] }
    ]
  }
}
```

The `permissions.allow` block also gets fixed syntax (`Edit(**/*.md)` not the invented `Edit(file_path://**/*.md)`) for paths outside `.claude/`. The hook handles the protected zone.

### How the hook short-circuits the prompt

Per the [hook schema](https://code.claude.com/docs/en/hooks), a `PreToolUse` hook can output JSON:

```json
{
  "hookSpecificOutput": {
    "hookEventName": "PreToolUse",
    "permissionDecision": "allow",
    "permissionDecisionReason": "..."
  }
}
```

The hook command in our config is a static `echo` of this JSON — no parsing of stdin needed because the `if: "Edit(**/*.md)"` field already filters to only `.md` edits before the command runs.

---

## Settings file scope

| File | Scope | Git | What goes here |
|---|---|---|---|
| `.claude/settings.json` | Project, team-wide | Committed | Hooks, permission rules everyone needs (the .md auto-allow). |
| `.claude/settings.local.json` | Project, this machine | Gitignored | Personal allowlist (testing tools, broad bash patterns, machine-specific paths). |
| `~/.claude/settings.json` | User-global | Per-machine | `defaultMode: bypassPermissions` lives here. Anything you want across all projects. |

Load order: user → project → local. Later overrides earlier.

### `defaultMode: bypassPermissions` MUST live in user settings

Claude Code silently ignores `defaultMode: bypassPermissions` when set in either project file (`settings.json` or `settings.local.json`). The escalation has to come from a source you own outright, not the project tree — otherwise an attacker could put bypass mode in a repo they trick you into opening. Allowed sources for bypass:

1. CLI flag at launch: `claude --dangerously-skip-permissions` (or `--permission-mode bypassPermissions`).
2. User-global settings: `~/.claude/settings.json` → `{"permissions":{"defaultMode":"bypassPermissions"}}`.

**This project's setup:** bypass lives in `~/.claude/settings.json` (Rohan's user file). Every session, every project starts in bypass. To restore the safety net for a specific session, launch with `claude --permission-mode default`. To restore it permanently, remove the key from the user settings file. **Don't add it back to `.claude/settings.local.json`** — it'll be silently dropped and the next session's mystery prompts will gaslight you for an hour.

---

## Testing-flow allowlist

For running `.claude/testing/flows/*` scenarios end-to-end without prompts, the project's `settings.local.json` allows:

**Browser MCP (Claude in Chrome):**
- `tabs_context_mcp`, `tabs_create_mcp`, `tabs_close_mcp`
- `navigate`, `computer`, `browser_batch`, `javascript_tool`
- `find`, `read_page`, `form_input`, `get_page_text`
- `read_network_requests`, `read_console_messages`
- `list_connected_browsers`, `switch_browser`

**State setup (WP CLI + MySQL):**
- `Bash(wp option *)` — read/update plugin options
- `Bash(wp eval *)` — running PHP via WP CLI
- `Bash(mysql -uroot updev *)` — queries against the dev DB
- `Bash(mysql -uroot -e *)` — common form
- `Bash(mysqldump -uroot updev *)` — snapshot/restore for state contracts (see `testing/testing.md`)

**Read access:**
- `Read(.claude/testing/**)` — flow files + run logs

### Risk notes

- `Bash(mysql -uroot updev *)` is **broad**. It allows DROP TABLE etc. Acceptable on the dev DB only — guardrail is "we never point this at production." If we ever script production state setup, narrow the rule per-command.
- The browser MCP tools are listed individually (not wildcarded) so new tools added by the extension default to prompting until reviewed.

---

## Burst mode — `--dangerously-skip-permissions`

Two scenarios where pre-approved rules aren't enough:
- Large doc-update session touching many `.md` files (hook handles each fine, but you also want to skip the small approvals for git ops, file moves, etc).
- Heavy testing run that crosses the rule perimeter (new tool, new path, ad-hoc bash).

Launch with:

```
claude --dangerously-skip-permissions
```

Or the modern flag form:

```
claude --permission-mode bypassPermissions
```

First time you use either, Claude Code shows a one-shot acknowledgement dialog and remembers it via `skipDangerousModePermissionPrompt: true` in user settings. Silent thereafter.

**When NOT to use it:** normal coding sessions on production code, where prompts are useful safety. Don't make `defaultMode: bypassPermissions` the project default — it removes the net for everyone.

In-session toggle: **Shift+Tab** cycles permission modes (default → acceptEdits → plan → bypassPermissions). Or run `/permissions` and pick.

---

## Watcher caveat

Hooks added to `.claude/settings.json` mid-session are not guaranteed to reload automatically. The settings watcher only watches directories that had a settings file when the session started.

To force a reload:
1. Type `/hooks` — opening that menu reloads settings.
2. Or restart the Claude Code session.

After reload, `/hooks` also lets you inspect registered hooks. Verify the three `PreToolUse` entries (Edit, Write, MultiEdit) show up before relying on them.

---

## Verifying the .md auto-allow still works

Future Claude Code versions might change hook ordering vs. the hardcoded check. Smoke test:

1. Edit any `.md` file under `.claude/domain-knowledge/` (or any other protected subtree).
2. If the edit goes through silently, the hook is winning.
3. If a "sensitive file" prompt appears, the ordering changed. Either:
   - Update this doc and the hook approach, or
   - Move to bursts as the day-to-day workflow.

A sensible cadence: re-verify after every Claude Code upgrade, or whenever an .md edit unexpectedly prompts.

---

## When to update this doc

- Permission rule failed where it should have worked → record what didn't match.
- A new tool/MCP/Bash pattern needs adding to the testing allowlist → add it here, then to `settings.local.json`.
- Claude Code adds a new mechanism for handling protected paths → record and migrate if cleaner than the hook.
- The hook stops working after a Claude Code upgrade → record the regression, mitigation, and any new approach.

---

## Cross-references

- Operating manual + autonomy levels: `/CLAUDE.md`
- Deploy & release operations: `deploy-and-release.md`
- Testing methodology + flow index: `../testing/testing.md`
