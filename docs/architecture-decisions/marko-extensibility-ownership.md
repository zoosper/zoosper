# Marko extensibility ownership

## Decision

Marko Plugins are the canonical future general method-interception runtime for Zoosper. Phase 10BI-A proves the installed Marko 0.8 line can discover an attributed plugin, resolve it through the Marko container, modify arguments before a call, modify the result after a call, and leave non-plugged resolutions unwrapped.

The existing Zoosper method-plugin subsystem is now compatibility-only. It is not expanded with new production consumers. Removal requires a later inventory proving that its disabled mode, report-only mode, layered configuration, module discovery, ordering and any current consumers are either unused or migrated. Around-style interception is not carried forward as a public extension promise.

Zoosper service decorators remain supported for explicit whole-service wrapping through module `service_decorators.php` contributions.

Zoosper entity-save lifecycle remains separate because it owns ordered, abort-capable persistence orchestration and mutable save context.

Zoosper general events remain authoritative until a dedicated event-convergence phase proves that Marko observers can preserve Zoosper's tested continue-after-listener-failure behaviour.

Marko Preferences remain unavailable to Zoosper modules until normal Zoosper service resolution is intentionally converged on the Marko container. Attribute discovery alone must not be presented as runtime support.

## Guardrails

- Do not run two permanent general plugin runtimes for the same production service.
- Do not add new production consumers to `Zoosper\\Core\\Plugin`.
- Prefer interface-targeted Marko plugins.
- Keep business services free of direct container access.
- Require direct Composer ownership for every Marko contract used by Zoosper Core.
