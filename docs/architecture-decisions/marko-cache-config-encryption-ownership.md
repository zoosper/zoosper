# Marko cache, configuration and encryption ownership

## Current truth

The Phase 10BF-10BH inventory found direct Marko cache, configuration and encryption contracts in active Core composition. Core currently constructs file or Redis cache drivers, exposes the Marko cache contract to Page composition, adapts Zoosper configuration to the Marko configuration contract for HTTP, console and Session composition, and supplies Marko encryption configuration to signed Redis cache values.

These dependencies are active runtime boundaries, not unused manifest entries. They must not be removed or moved as one blind dependency-only change.

## Decisions

- Phase 10BI extracted `zoosper/cache`, which owns `marko/cache`, `marko/cache-file`, `marko/cache-redis` and the cache-specific use of `marko/encryption`. It exposes a Zoosper-owned cache contract, and Core and Page no longer import Marko Cache directly.
- Phase 10BJ extracted `zoosper/config` as the shared compatibility bridge for HTTP boot, console composition and Session. Core retains application loading, module aggregation, project overrides and persisted scoped configuration; Cache retains its independent package-local adapter.
- Encryption is not extracted independently while its only captured Core use is cache signing configuration. Its ownership should move with `zoosper/cache` unless a separate non-cache consumer is discovered.
- Existing public configuration keys, file and Redis behaviour, fail-open page-cache semantics and module-removal behaviour must remain unchanged.

## Guardrails

- Do not add new direct `Marko\\Cache`, `Marko\\Config` or `Marko\\Encryption` imports outside their current captured boundaries.
- Do not create empty same-name packages before runtime ownership and public contracts move with them.
- Do not make feature modules depend on concrete Marko cache drivers.
- Require focused real file-cache, Redis object-graph, frontend boot, console recovery and full-suite proof for extraction.

## Phase 10BK scoped configuration ownership

Phase 10BK extracted `zoosper/scoped-config` as the independent persisted scope foundation shared by Admin, Mail, Settings and Theme. Settings retains catalogue, mutation, validation, audit and presentation workflows. Core retains application configuration loading and registers one shared scoped repository service.
