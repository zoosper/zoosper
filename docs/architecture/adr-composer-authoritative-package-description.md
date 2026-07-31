# ADR: Composer-Authoritative Package Description

## Status

Accepted and implemented.

## Context

The theme package described itself in both `composer.json` and `module.php`.
The module registry does not consume module descriptions, while Composer
manifests are the standard package metadata surface used by repositories,
dependency tools and future Marko package discovery.

## Decision

First-party package descriptions live only in `composer.json`.

Remove `description` from first-party `module.php` files and retain the richer
theme description in its Composer manifest. An architecture test requires a
non-empty Composer description and rejects first-party module-file descriptions.

## Consequences

- Package metadata has one authoritative description.
- Module files continue shrinking towards wiring and genuinely operational
  configuration.
- Package catalogues and Composer tooling see the same description as Zoosper.
