# Marko extensibility ownership

## Decision

Zoosper retains bridge-first package ownership. Feature and platform modules consume Zoosper-owned contracts and adapters; the focused Zoosper bridge package owns the corresponding Marko package and implementation dependency.

Direct Marko package dependencies in a feature or platform module are permitted only where the Marko contract is deliberately part of that module's public boundary and no same-capability Zoosper bridge exists. Transitive availability is never treated as dependency ownership.

The Phase 10BI-A compatibility proof established that installed Marko 0.8 Plugins can modify arguments before a call, modify results after a call and avoid wrapping services without plugins. That proof does not make `zoosper/core` the owner of Marko's container or plugin runtime. The direct `marko/core` requirement and Core-local compatibility fixture are therefore retired.

A future production need for Plugins, Preferences, container integration or Marko observers must first introduce or select a focused Zoosper bridge boundary. To avoid unnecessary package proliferation, the preferred initial boundary is `zoosper/extensibility`, provided it remains a thin adapter and does not duplicate Marko.

The existing `Zoosper\\Core\\Plugin` subsystem has no production consumer or module plugin manifest in the Phase 10BI-B inventory. It remains frozen compatibility code until a dedicated removal change proves its complete test and documentation closure.

Zoosper service decorators remain supported for explicit whole-service wrapping. Zoosper entity-save lifecycle remains separate because it owns ordered, abort-capable persistence orchestration and mutable save context. Zoosper general events remain authoritative because their tested continue-after-listener-failure behaviour differs from the captured Marko observer dispatcher.

## Phase 10BE retirement

Phase 10BE removed the unused `Zoosper\Core\Plugin` subsystem after repository-wide inventory found no production consumer or module `method_plugins.php` manifest. General method interception remains unavailable until a real production use case introduces a thin Zoosper-owned bridge over Marko.

## Guardrails

- Do not add direct `marko/core` ownership to `zoosper/core` for compatibility tests or future runtime experiments.
- Do not recreate `Zoosper\Core\Plugin` or another Zoosper-native general interceptor framework.
- Do not run two permanent general interception runtimes for the same production service.
- Put Marko implementation dependencies in the focused Zoosper bridge that owns the capability.
- Keep business services free of direct container access.
- Preserve the entity-save lifecycle and general-event semantics until separately proven replacements exist.
