# ADR: Composer-Authoritative Module Identity

## Status

Accepted and implemented.

## Context

Zoosper packages have both Composer manifests and `module.php`. Allowing the
runtime name and version in `module.php` to override Composer creates two
sources of package identity and permits drift between dependency management,
diagnostics and the compiled module graph.

## Decision

For a valid `type=zoosper-module` package with `extra.marko.module=true`:

- runtime name comes from Composer `name`, replacing `/` with `-`;
- runtime version comes from Composer `version` when present;
- `module.php` remains the transitional source of enabled state, sort order and
  Zoosper-specific configuration;
- packages without valid Composer/Marko metadata retain the existing fallback
  only while application and manually installed module discovery is migrated.

## Consequences

- Composer becomes authoritative for package identity.
- A stale or misleading `module.php` name cannot change runtime identity.
- Compiled diagnostics map directly to installed package names.
- The remaining identity fallback can be removed once every runtime home is
  discovered through Composer-aware Marko metadata.
