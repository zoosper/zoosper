# ADR: Deterministic Runtime Module Layer Order

## Status

Accepted and implemented for the transitional registry.

## Context

The registry globally sorted modules by arbitrary `sort_order` values stored in
`module.php`. Only two first-party packages used the field, and it could place a
higher-priority application module before or after package defaults without
expressing the real override hierarchy.

Marko's runtime homes already define priority: vendor is the base layer,
`modules/` overrides vendor, and `app/` overrides both.

## Decision

Runtime processing order is deterministic and follows:

1. `vendor`, base defaults;
2. `modules`, third-party overrides;
3. `app`, application overrides.

Within each layer, modules are ordered by canonical runtime name. Remove
`sort_order` from first-party `module.php` files and stop using it for global
first-party ordering.

The field remains readable only as a compatibility property on the transitional
`Module` value object until the Zoosper registry is replaced by Marko's graph.

## Consequences

- Loader order and conflict priority express the same hierarchy.
- Application overrides are processed after package defaults.
- Module processing is reproducible without magic numbers.
- Feature-specific ordering remains in its owning configuration, such as admin
  menu or asset sort order, rather than global module metadata.
