# Current development line

## Version

`0.3.2-alpha.1-dev`

## Release baseline

- Latest immutable release: `v0.3.1-alpha.1`.
- Release commit: `bfde15051396cd1dd19bdfaaa7b560e9c29e8287`.
- Active branch: `dev`.
- Zoosper remains public alpha software. No stable release has shipped.

## Development direction

The 0.3.2 line is the beta-readiness public-alpha progression after the substantial 0.3.0 alpha series. Continue product-facing CMS capability, extension ergonomics, API parity, documentation, and release hardening while preserving the verified alpha.5 security, ownership, data-integrity, and presentation boundaries.

## Release discipline

- Keep runtime identity, API health output, Admin presentation, changelog, roadmap, and public documentation aligned.
- Keep architecture documentation and each package README current with every phase.
- Preserve clean worktrees, bounded diffs, full tests, strict quality checks, Composer validation, dependency audit, manifest freshness, foreign-key reconciliation, and runtime smoke evidence before each release.
- Do not modify or retarget the immutable `v0.3.1-alpha.1` tag.

## Beta-readiness Composer policy

The 0.3.2 alpha development line removes first-party `dev-dev` dependency coupling. All 32 first-party path packages receive the explicit `0.3.2-alpha.1` Composer package candidate from the root repository map and consume compatible first-party packages through `^0.3.1@alpha`. This keeps runtime identity at `0.3.2-alpha.1-dev` while making package compatibility independent of the Git development branch.
