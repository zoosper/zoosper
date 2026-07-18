# Phase 1.37g — Composer-installed module discovery

## Goal

Teach `ModuleRegistry` to discover Composer-installed Zoosper modules from package metadata so modules can move towards `vendor/zoosper/*` like true Composer packages.

## Scope

- Add immutable `Module` metadata object.
- Extend `ModuleRegistry` to scan app, packages, modules and Composer vendor package metadata.
- Deduplicate modules by real path and module name.
- Add verification tool and regression tests.
- Document vendor module discovery.

## Out of scope

- Removing the `app/zoosper-media` compatibility symlink.
- Moving additional modules.
- Publishing packages to a package registry.

## Next phase

Remove the `app/zoosper-media` compatibility symlink after real-repo verification proves vendor/package discovery works cleanly.
