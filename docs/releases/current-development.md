# Current development line

## Version

`0.3.2-alpha.3-dev`

## Release baseline

- Latest immutable release: `v0.3.2-alpha.2`.
- Release commit: `dcdbeedc2f7d394481afc2ae889a7f23205c7730`.
- Previous immutable release: `v0.3.2-alpha.1` at `f75f5cb3591be11555e5ca7504585c7d98225b62`.
- Active branch: `dev`.
- Zoosper remains public alpha software. No stable release has shipped.

## Development direction

The 0.3.2 line is the beta-readiness public-alpha progression after the substantial 0.3.0 alpha series. Continue product-facing CMS capability, extension ergonomics, API parity, documentation, and release hardening while preserving the verified security, ownership, data-integrity, presentation, artifact, and API Grid boundaries shipped through `v0.3.2-alpha.1`.

## Release discipline

- Keep runtime identity, API health output, Admin presentation, changelog, roadmap, and public documentation aligned.
- Keep architecture documentation and each package README current with every phase.
- Preserve clean worktrees, bounded diffs, full tests, strict quality checks, Composer validation, dependency audit, manifest freshness, foreign-key reconciliation, and runtime smoke evidence before each release.
- Do not modify or retarget the immutable `v0.3.2-alpha.2` tag.

## Beta-readiness Composer policy

The 0.3.2 alpha development line removes first-party `dev-dev` dependency coupling. All 33 first-party path packages retain the explicit installable `0.3.2-alpha.2` Composer package candidate from the root repository map and consume compatible first-party packages through `^0.3.1@alpha`. This keeps the installable synchronized package candidate at `0.3.2-alpha.2` while runtime identity advances to `0.3.2-alpha.3-dev`, keeping package compatibility independent of the Git development branch.
