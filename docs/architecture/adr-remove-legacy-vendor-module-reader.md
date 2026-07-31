# ADR: Remove Legacy Vendor Module Metadata Reader

## Status

Accepted and implemented before public release.

## Context

First-party manifests and the package scaffolder now use only Marko module
identity. The transitional registry still accepted `extra.zoosper.module` from
Composer-installed packages, preserving a second identity contract despite the
project having no public compatibility obligation.

## Decision

Composer-installed Zoosper modules must declare both:

```json
"type": "zoosper-module",
"extra": { "marko": { "module": true } }
```

Their runtime metadata file is the conventional package-root `module.php`.
Legacy-only `extra.zoosper` packages are ignored.

## Consequences

- Marko is the single Composer module identity authority.
- Vendor discovery has one path and one conventional metadata location.
- Old private packages must update their Composer manifest before installation.
- The transitional registry is smaller, but still remains until all module
  consumers move to Marko's application module graph.
