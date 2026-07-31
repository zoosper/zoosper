# ADR: Retire Legacy Zoosper Composer Metadata

## Status

Accepted and implemented for first-party packages and scaffolding.

## Context

First-party package manifests temporarily declared both `extra.marko` and
`extra.zoosper`. Phase 1G made canonical Marko identity operational for
Composer-installed Zoosper modules, and Phase 1H made Composer-installed
`vendor/` the runtime home for extracted packages.

Keeping dual identity metadata would preserve two competing module systems and
encourage new packages to copy obsolete configuration.

## Decision

First-party package manifests use only:

```json
"extra": {
    "marko": {
        "module": true
    }
}
```

`PackageModuleScaffolder` generates this metadata and the bounded `dev-dev`
internal dependency used during pre-release development.

The transitional registry may continue reading legacy metadata for third-party
packages during a bounded compatibility period, but Zoosper itself no longer
produces or requires it.

## Consequences

- Marko is the single package identity authority for first-party modules.
- Newly scaffolded packages cannot reintroduce legacy metadata.
- Existing third-party legacy packages remain discoverable temporarily.
- The remaining legacy parser can be deleted when external compatibility policy
  is finalised before public release.
