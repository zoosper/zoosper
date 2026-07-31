## Claude Instructions for Zoosper

@AGENTS.md

### Marko tooling direction

This project follows Marko's AI-assisted development pattern:
- AGENTS.md contains shared project-wide guidance.
- CLAUDE.md is the Claude entrypoint and should stay lean.
- .claude/settings.json enables Marko-aware Claude Code plugins.
- Documentation must be updated as code evolves — including
  ROADMAP.md's daily log, which doubles as this project's continuity
  mechanism across sessions.

When adding code, favour complete readable files over fragments. Do not
infer from older compressed files if a clearer pattern exists in the docs.
