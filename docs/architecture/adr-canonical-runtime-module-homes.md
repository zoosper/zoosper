# ADR: Canonical Runtime Module Homes

## Status

Accepted and implemented for the transitional Zoosper registry.

## Context

The registry historically scanned `app/`, `packages/`, `modules/` and
`vendor/`. This mixed repository source layout with runtime discovery and made
`packages/` a fourth runtime priority layer.

Marko's documented runtime homes are `vendor/`, `modules/` and `app/`.
First-party packages under repository `packages/` are already exposed through
Composer path repositories and therefore appear under `vendor/` at runtime.

## Decision

Stop scanning `packages/*/module.php` directly.

The canonical runtime homes are now:

1. `vendor/`, lowest priority;
2. `modules/`, middle priority;
3. `app/`, highest priority.

The repository may retain `packages/` as first-party monorepo source. Composer
is responsible for installing or symlinking it into `vendor/` for runtime use.

## Consequences

- Runtime discovery aligns with Marko's three-layer model.
- Repository source layout no longer changes module priority.
- Extracted packages are tested through their installed Composer identity.
- A missing Composer install/dump-autoload is surfaced rather than hidden by a
  direct source scan.
- The remaining Zoosper registry is still transitional; replacing it with the
  Marko module graph remains the next architectural destination.
