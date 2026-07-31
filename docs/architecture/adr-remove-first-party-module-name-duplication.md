# ADR: Remove First-Party Module Name Duplication

## Status

Accepted and implemented.

## Context

Phase 1L made Composer package names authoritative for valid Marko-native
Zoosper modules. First-party `module.php` files still repeated the same runtime
identity in a `name` key, leaving stale duplicate declarations that runtime no
longer needed.

## Decision

Remove `name` from all first-party `module.php` files.

Composer `name` is the only first-party package identity. `ModuleRegistry`
derives the runtime name by replacing `/` with `-`.

`module.php` remains temporarily responsible for Zoosper-specific runtime
metadata such as enabled state, sort order and descriptions. Existing version
fields remain until package version ownership is completed separately.

## Consequences

- First-party identity has one source of truth.
- Renaming a package cannot leave a contradicting module name behind.
- Manually installed transitional modules without valid Composer metadata may
  still use the registry fallback until the Marko graph fully replaces it.
- An architecture test prevents first-party `name` keys from returning.
