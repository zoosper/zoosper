# ADR: Legacy Local Module Boundary

## Status

Accepted and enforced.

## Context

The repository contains canonical first-party packages as direct children of
`app/` and `packages/`, while legacy/local code may exist below `app/code/`.
Recursive architecture scans incorrectly classified nested local Composer
packages as first-party Marko modules.

The transitional runtime registry already discovers application modules only
from direct `app/*/module.php` entries. Nested `app/code/*/*` packages are not
part of this canonical application-module layer.

## Decision

Canonical first-party package scope is limited to:

```text
app/*
packages/*
```

Canonical application runtime discovery is limited to direct `app/*` modules.
Nested `app/code/` packages are treated as legacy/local code and are not subject
to first-party Marko metadata guards unless they are deliberately migrated into
a canonical module home.

A regression test proves both the runtime and architecture-test boundaries.

## Consequences

- Local packages are not silently forced into first-party framework contracts.
- First-party tests remain strict for all canonical Zoosper packages.
- Moving a local package into `app/`, `modules/` or Composer-installed `vendor/`
  is an explicit migration, not an accidental recursive discovery effect.
- `app/code/` remains a legacy location and should not receive new framework
  capabilities.
