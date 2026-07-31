# ADR: Module Scaffolder Metadata Contract

## Status

Accepted and implemented.

## Context

Existing first-party modules were migrated to Composer-owned name, version,
description and Marko identity, but both module scaffolders still generated the
retired `module.php` keys. The application scaffolder also omitted a package
manifest entirely.

## Decision

Both `make:module` and `make:package-module` generate:

- a `type=zoosper-module` Composer manifest;
- `extra.marko.module=true`;
- bounded `zoosper/core=dev-dev` during pre-release development;
- PSR-4 package metadata;
- an empty `module.php` reserved for future Marko wiring.

Generated `module.php` files must not declare name, version, description,
enabled state or arbitrary default sort order.

## Consequences

- New modules start on the current architecture rather than requiring cleanup.
- Application and extracted-package generators share the same identity contract.
- The application module README may still document root Composer/autoload
  integration until the generator safely automates that repository change.
- A behavioural test executes both scaffolders and locks the generated contract.
