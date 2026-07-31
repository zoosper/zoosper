# ADR: First-Party Module Enable State

## Status

Accepted and implemented.

## Context

Every first-party package is installed through Composer and declares
`extra.marko.module=true`. Its `module.php` still repeated `enabled=true`, even
though absence of the key already resolves to enabled in the transitional
registry.

The duplicate flag added no ability to disable a package at deployment time:
editing vendor or application package source is not an appropriate operational
configuration mechanism.

## Decision

Remove `enabled=true` from all first-party `module.php` files.

A first-party package participates because it is installed, is a
`zoosper-module`, and declares Marko module identity. Future environment-level
module disablement, if required, must be implemented as application
configuration or Marko graph policy rather than by editing package source.

The transitional registry keeps its default-enabled behaviour so manually
installed modules remain compatible during migration.

## Consequences

- First-party module files contain less redundant metadata.
- Installed Marko package identity is the activation signal.
- Package source does not masquerade as deployment configuration.
- An architecture test prevents first-party `enabled` keys from returning.
