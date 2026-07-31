# ADR: Internal Package Version Constraints During Development

## Status

Accepted for the pre-release `dev` branch.

## Context

The root project and first-party package manifests used `*@dev` for internal
`zoosper/*` dependencies. Composer accepts that constraint but warns because it
is unbounded: it can match any development version of the package.

Zoosper has not published its first 0.x package release and all path repository
packages are currently locked as `dev-dev` in `composer.lock`.

## Decision

Pin every current first-party `zoosper/*` dependency to the exact development
branch version:

```json
"zoosper/core": "dev-dev"
```

This is a development-only stability contract. It removes the unbounded
constraint without falsely claiming a released semantic version or changing the
existing locked package versions.

The first public package line will replace these branch pins in one coordinated
release phase, expected to use bounded 0.x constraints after package versions
and tags exist.

## Consequences

### Positive

- `composer validate` no longer reports unbounded internal constraints.
- Every internal dependency targets the branch represented by the current lock.
- No premature semantic-version compatibility promise is introduced.
- Root and cross-package constraints use one consistent policy.

### Negative

- Renaming the development branch requires updating these constraints and the
  lock file together.
- `dev-dev` is intentionally unsuitable for a public stable release.

## Enforcement

An architecture test scans the root and first-party Composer manifests. Every
internal `zoosper/*` dependency must be bounded and must not use `*@dev` or `*`.
