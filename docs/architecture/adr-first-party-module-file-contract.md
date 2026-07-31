# ADR: First-Party Module File Contract

## Status

Accepted and enforced.

## Context

The Marko reconciliation phases removed package identity, version,
description, enabled state and global sort order from first-party `module.php`
files. Without one consolidated contract, any retired key could return through
manual edits or future generators.

Marko documents `module.php` as optional module wiring. Its supported concerns
include dependency bindings, singletons, module sequence and boot wiring.

## Decision

First-party `module.php` files may contain only these top-level keys:

- `bindings`;
- `singletons`;
- `sequence`;
- `boot`.

`bindings`, `singletons` and `sequence` must be arrays. `boot` must be callable.
An empty array is valid for a module that currently needs no Marko wiring.

Package identity, version, description and module participation remain owned by
Composer and Marko package metadata. Feature-specific ordering remains in the
feature configuration that owns it.

## Consequences

- The completed metadata cleanup is protected by one clear architecture guard.
- Module files have an explicit purpose rather than acting as generic metadata
  buckets.
- New Marko wiring can be added without weakening package ownership boundaries.
- Unsupported keys fail with the exact file path and key names.
