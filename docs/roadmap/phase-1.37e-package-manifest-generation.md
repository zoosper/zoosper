# Phase 1.37e — Package Manifest Generation

## Goal

Create package-ready `composer.json` manifests for first-party modules before physically extracting any module to a separate repository or path repository.

## Scope

- Generate module-level composer manifests for source modules.
- Add manifest generator and verifier tools.
- Add tests for manifest generation.
- Document package manifest conventions.

## Deliberately out of scope

- Moving modules to `packages/`.
- Removing root PSR-4 mappings.
- Publishing packages.
- Composer plugin integration.

## Next phase

Phase 1.37f should pilot `zoosper-media` as the first Composer path repository.
