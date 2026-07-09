# Marko and Claude Setup Notes

Zoosper includes Claude guidance files inspired by Marko's devai workflow.

## Files

- `AGENTS.md`: shared AI/developer guidelines.
- `CLAUDE.md`: Claude-specific entrypoint.
- `.claude/settings.json`: Marko marketplace/plugin configuration.
- `.claude/commands/`: project-specific Claude command prompts.

## Manual Claude Code check

Open Claude Code in the repository root and verify that `.claude/settings.json` contains:

- `extraKnownMarketplaces.marko`
- `enabledPlugins.marko-skills@marko`
- `enabledPlugins.marko-lsp@marko`
- `enabledPlugins.marko-mcp@marko`

If Marko's official devai installer changes its schema, prefer the official Marko output and keep these project instructions as the Zoosper-specific layer.
