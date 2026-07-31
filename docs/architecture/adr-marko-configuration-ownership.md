# ADR: Marko Configuration Ownership

## Status

Accepted and implemented for the shared HTTP/CLI assembly path in Phase 1B.

## Context

Zoosper currently has `ConfigRepository`, `ModuleConfigAggregator`, several
layered loaders and `MarkoConfigRepositoryAdapter`. HTTP boot aggregates module
settings and root overrides, while the CLI can load root configuration through
`ConfigRepository::fromPath()`.

The adapter proves that parts of the codebase already recognise Marko's
configuration contract, but the Zoosper repository remains the canonical
runtime object.

## Decision

`Marko\Config\ConfigRepositoryInterface` becomes the canonical configuration
contract.

The first code migration will establish one configuration assembly path used by
HTTP and CLI. Required precedence is:

1. framework or package defaults;
2. enabled module defaults;
3. project root overrides;
4. environment-derived values at the defined boundary;
5. explicit runtime values where unavoidable.

The existing Zoosper adapter may be used only as a migration seam. It is not the
permanent owner of configuration. Once consumers and source loading are Marko-
native, duplicate Zoosper repositories and root-only CLI loading are removed.

## Behaviour that must be locked before migration

- Dot-notation lookup.
- Missing-key behaviour.
- Typed string, integer, boolean and array access.
- Module default plus project override precedence.
- Nested associative merge semantics.
- List replacement or append semantics for every affected configuration type.
- Disabled module exclusion.
- HTTP and CLI parity.
- Secret redaction in diagnostics.

## Required tests for the implementation phase

- A module default is visible in HTTP and CLI.
- A project override wins in both runtimes.
- Missing and invalid typed values fail with actionable errors.
- Invalid database configuration does not break database-free CLI commands.
- Configuration conflicts and malformed files fail through the registered
  error path.

## Consequences

Configuration consolidation precedes CLI replacement because command discovery
and lazy service construction need one dependable configuration source.

## Phase 1B implementation

- `ApplicationConfigLoader` is now the single configuration assembly entry point.
- HTTP, `bin/zoosper` and `bin/zoosper-schema` use the same module-default and
  project-override path.
- The application container exposes `Marko\Config\ConfigRepositoryInterface`
  through the existing adapter while legacy consumers migrate.
- The legacy `ConfigRepository` remains a compatibility implementation for this
  phase; it is no longer loaded directly by the primary CLI entry points.
- CLI database laziness is intentionally reserved for the CLI ownership phase.
