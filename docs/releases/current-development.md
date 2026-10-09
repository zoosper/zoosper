# Current development line

## Version

`v0.3.2-alpha.4` published public-alpha baseline; no next release version selected

## Release baseline

- Latest immutable release: `v0.3.2-alpha.4` at `74db4e87c43999da84e59329dab6ff83d399dbb6`.
- Previous immutable release: `v0.3.2-alpha.3` at `4676b012bc579d6f66f20aa317c4da862d5f045a`.
- Active branch: `dev`.
- Zoosper remains public alpha software. No stable release has shipped.

## Development direction

The 0.3.2 line is the beta-readiness public-alpha progression after the substantial 0.3.0 alpha series. Continue only the required C1-C5 closure work in ROADMAP.md, without adding new product features while preserving the verified security, ownership, data-integrity, presentation, artifact, and API Grid boundaries shipped through `v0.3.2-alpha.1`.

## Release discipline

- Keep runtime identity, API health output, Admin presentation, changelog, roadmap, and public documentation aligned.
- Keep architecture documentation and each package README current with every phase.
- Preserve clean worktrees, bounded diffs, full tests, strict quality checks, Composer validation, dependency audit, manifest freshness, foreign-key reconciliation, and runtime smoke evidence before each release.
- Do not modify or retarget the immutable `v0.3.2-alpha.4` or `v0.3.2-alpha.3` tags.
- Alpha.4 publication does not claim production deployment acceptance or final programme closure.

## Beta-readiness Composer policy

The 0.3.2 alpha development line removes first-party `dev-dev` dependency coupling. All 33 first-party path packages retain the explicit installable `0.3.2-alpha.4` Composer package version from the root repository map and continue to consume compatible first-party packages through `^0.3.1@alpha`. The published release keeps package compatibility independent of the Git branch name.
