# Phase 1.37q - Vendor package discovery audit and docs

## Goal

Prove and document the next stage of Zoosper's blank-USB modularity model: modules should be discoverable from Composer-installed `vendor/` packages, not only from `app/` or local `packages/` paths.

## Implemented

- Added `tools/audit-vendor-package-discovery.php`.
- Added vendor package discovery fixture tests.
- Added audit-tool contract tests.
- Documented the Composer package contract for vendor modules.
- Documented the local path repository workflow.
- Documented Marko-style media package split inspiration for future `zoosper/media-gd` and `zoosper/media-imagick` packages.

## Why this matters

Phase 1.37p can generate package modules under `packages/`. Phase 1.37q verifies the architecture direction for the later state where packages can live under `vendor/`.

## Non-goals

This phase does not build a marketplace, publish packages, implement package uninstall, or add actual GD/Imagick processors.

## Next phase options

- Phase 1.37n.1: local media derivative processor implementation behind `MediaProcessorInterface`.
- Phase 1.37r: behaviour tests for media upload controller cleanup/orphan-file failure path.
- Phase 1.38: RoleAdminController Latte/template migration.
