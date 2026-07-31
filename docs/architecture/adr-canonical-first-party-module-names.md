# ADR: Canonical First-Party Runtime Module Names

## Status

Accepted and implemented.

## Context

Application modules used kebab-case runtime names such as `zoosper-page`, while
the extracted packages used legacy underscore names such as `Zoosper_Media` and
`Zoosper_Errors`. Their Composer package identities are `zoosper/media` and
`zoosper/errors`.

Dual naming complicates collision detection, diagnostics, compiled manifests
and future Marko module graph adoption.

## Decision

A first-party runtime module name is derived from its Composer package name by
replacing `/` with `-`:

```text
zoosper/media  -> zoosper-media
zoosper/errors -> zoosper-errors
```

All first-party `module.php` files must follow this rule. An architecture test
scans application and extracted package manifests and locks this parity.

## Consequences

- The compiled module graph has one predictable naming convention.
- Runtime identity maps directly to Composer identity.
- Legacy underscore aliases are not retained because Zoosper is pre-release.
- Cache/manifest compilation must run after this change.
