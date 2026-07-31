# ADR: Marko Framework Boundary

## Status

Accepted.

## Context

Zoosper contains mature CMS features, but `zoosper-core` also contains generic
configuration, module discovery, container, routing, event, plugin, cache and
console infrastructure. Marko packages are already installed and are intended
to provide reusable framework mechanics.

Without an explicit boundary, each new generic subsystem risks creating a
second framework inside the CMS.

## Decision

Marko is the framework. Zoosper is the CMS product.

Marko owns generic mechanics when an adequate Marko package exists. Zoosper
owns CMS domain concepts and may provide narrow adapters, policies and
integrations around Marko contracts.

Before implementing any new generic subsystem, contributors must:

1. Inspect the current Marko package catalogue and locked source.
2. Compare contracts, lifecycle, diagnostics, extension behaviour and tests.
3. Record ADOPT, ADAPT, KEEP, DEFER or REMOVE in the adoption matrix.
4. Build Zoosper infrastructure only when Marko lacks the capability or when
   the requirement is genuinely CMS-specific.

No backwards-compatibility bridge is required for obsolete internal
infrastructure before the first public release. Migrations should choose one
canonical runtime path and remove the superseded path in the same bounded
programme.

## Consequences

### Positive

- `zoosper-core` can shrink toward CMS composition rather than framework code.
- Generic capabilities benefit from Marko's module, contract and extension model.
- Zoosper effort stays focused on sites, pages, themes, media and administration.
- Duplicate infrastructure and contradictory boot paths become architecture defects.

### Negative

- Some existing Zoosper infrastructure will be retired.
- Marko is pre-1.0, so version upgrades require deliberate compatibility testing.
- Security-sensitive and persistence migrations require staged evidence rather
  than bulk replacement.

## Enforcement

Architecture tests should eventually prevent feature packages from importing
concrete infrastructure drivers. Reviewers must reject a new generic subsystem
unless the adoption matrix records why Marko is unsuitable.
