# Current development line

## Version

`0.3.2-alpha.4-dev`

## Release baseline

- Latest immutable release: `v0.3.2-alpha.3` at `4676b012bc579d6f66f20aa317c4da862d5f045a`.
- Previous immutable release: `v0.3.2-alpha.2` at `dcdbeedc2f7d394481afc2ae889a7f23205c7730`.
- Active branch: `dev`.
- Zoosper remains public alpha software. No stable release has shipped.

## Development direction

The 0.3.2 line is the beta-readiness public-alpha progression after the substantial 0.3.0 alpha series. Continue product-facing CMS capability, extension ergonomics, API parity, documentation, and release hardening while preserving the verified security, ownership, data-integrity, presentation, artifact, and API Grid boundaries shipped through `v0.3.2-alpha.1`.

## Release discipline

- Keep runtime identity, API health output, Admin presentation, changelog, roadmap, and public documentation aligned.
- Keep architecture documentation and each package README current with every phase.
- Preserve clean worktrees, bounded diffs, full tests, strict quality checks, Composer validation, dependency audit, manifest freshness, foreign-key reconciliation, and runtime smoke evidence before each release.
- Do not modify or retarget the immutable `v0.3.2-alpha.3` tag.

## Beta-readiness Composer policy

The 0.3.2 alpha development line removes first-party `dev-dev` dependency coupling. All 33 first-party path packages retain the explicit installable `0.3.2-alpha.3` Composer package candidate from the root repository map and consume compatible first-party packages through `^0.3.1@alpha`. Runtime development continues on `0.3.2-alpha.4-dev`, keeping package compatibility independent of the Git development branch.
