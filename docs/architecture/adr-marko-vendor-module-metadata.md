# ADR: Marko Metadata for Composer-Installed Zoosper Modules

## Status

Accepted and implemented as a transitional runtime step.

## Context

First-party packages now declare `extra.marko.module=true`, but the existing
Zoosper vendor-package discovery still required `extra.zoosper.module`. That
made canonical Marko metadata decorative rather than operational.

Marko identifies modules through `extra.marko.module=true`, and `module.php` is
optional framework wiring at the package root. Zoosper still needs its current
CMS module metadata file until all loaders move to the Marko module graph.

## Decision

Composer-installed packages with both:

```json
"type": "zoosper-module",
"extra": { "marko": { "module": true } }
```

are discovered using a root `module.php` without requiring `extra.zoosper`.
Legacy `extra.zoosper.module` remains supported during migration.

The `type=zoosper-module` guard is intentional. It prevents the transitional
Zoosper registry from importing generic Marko framework packages into Zoosper's
legacy config loaders merely because those packages are Marko modules.

## Consequences

- New Composer-installed Zoosper modules can use canonical Marko identity.
- Existing modules remain compatible during the bounded transition.
- Generic Marko packages stay owned by the Marko application graph.
- `extra.zoosper` can be removed from package manifests after all remaining
  Zoosper loaders consume the canonical module graph.
