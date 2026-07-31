# ADR: Marko Module Ownership and Runtime Locations

## Status

Accepted. First-party package identity implemented in Phase 1D; runtime discovery migration remains staged.

## Context

The current module registry recognises multiple source locations and
deduplicates repeated identities. Repository source layout and runtime module
discovery have become mixed concerns.

Marko's peer-module model uses Composer package identity and explicit priority
layers. Zoosper needs predictable overrides and loud conflicts without adding
another module system.

## Decision

Every runtime module is a Composer package and a Marko module.

Runtime priority is:

1. `vendor/` for framework and Composer-installed packages, lowest priority.
2. `modules/` for manually installed third-party modules, middle priority.
3. `app/` for application customisations, highest priority.

The repository may keep first-party package source under `packages/` while
developing the monorepo. Composer path repositories make those packages
available to runtime discovery. `packages/` is not an additional runtime scan
layer.

Duplicate module identity at the same priority is an error. Higher-priority
overrides must be explicit and diagnosable. Silent deduplication is forbidden.

Zoosper first-party modules remain peers. `zoosper-core` must not receive
special extension rights merely because it boots early.

## Consequences

### Positive

- One package identity and one runtime discovery model.
- Project customisation can override vendor behaviour without patches.
- Ambiguity fails loudly.
- First-party packages can move to separate repositories without changing the
  runtime model.

### Negative

- Existing source locations and package metadata require migration.
- Module compilation and migration discovery must be audited before changing
  runtime discovery.

## Migration constraints

- Migrations must continue to use live package discovery and must never depend
  on stale compiled runtime metadata.
- The canonical runtime model must be proven before deleting `ModuleRegistry`.
- Compatibility scanning of old locations must not become permanent.

## Phase 1D implementation

All first-party Composer packages now declare:

```json
"extra": {
    "marko": {
        "module": true
    }
}
```

Existing `extra.zoosper` metadata remains temporarily while the current
`ModuleRegistry` is still the runtime discovery path. This is a bounded
transition, not a permanent dual module system.

An architecture test scans first-party `app/` and `packages/` Composer
manifests and fails if Marko module identity is missing. The next module phase
will compare and migrate runtime discovery, priority and conflict behaviour.

## Phase 1E implementation

The transitional registry now applies deterministic source priority:

1. `vendor` (lowest)
2. `modules`
3. monorepo `packages` source
4. `app` (highest)

The same real path is still deduplicated so Composer path-repository symlinks do
not produce false conflicts. Different modules claiming the same identity at
the same priority now throw `DuplicateModuleException` with both paths and a
remediation suggestion. A higher-priority identity explicitly replaces a lower-
priority one.

This closes the previous silent name-collision behaviour while runtime discovery
is migrated to Marko. The `packages` priority is transitional and disappears
when repository source layout is fully separated from runtime discovery.
