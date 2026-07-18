# Phase 1.37f — Media Path Repository Pilot

## Goal

Test the first Composer-installable module extraction using `zoosper-media` without changing all module discovery behaviour at once.

## Scope

- Move `app/zoosper-media` to `packages/zoosper-media` through a controlled tool.
- Keep `app/zoosper-media` as a compatibility symlink for current ModuleRegistry discovery.
- Add root Composer path repository and require entry for `zoosper/media`.
- Add pilot verifier and tests.
- Document operational and rollback steps.

## Out of scope

- Moving every module.
- Removing the `app/zoosper-media` compatibility path.
- Vendor module discovery in ModuleRegistry.
- Publishing packages to Packagist or private Composer registry.

## Next phase

Teach ModuleRegistry to discover Composer-installed Zoosper modules from package metadata so compatibility symlinks can be removed later.
