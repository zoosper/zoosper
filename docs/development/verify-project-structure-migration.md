# Verify Project Structure Migration Coverage

This document records the migration coverage for `tools/verify-project-structure.php`.

The source-contract intent of the legacy verify script is now preserved as durable Pest coverage.

## Current state

`tools/verify-project-structure.php` is marked `migrated` in the migration status ledger and has been retired from `tools/`.

Replacement Pest coverage lives in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyProjectStructureCoverageTest.php
```

Read-only audit tooling lives in:

```text
tools/audit-verify-project-structure-migration.php
```

## Migration result

The project-structure contract is now owned by Pest coverage instead of a legacy one-off verify script.

## Covered contract

The replacement Pest coverage verifies the stable repository structure needed by the tooling and modular architecture:

- root `composer.json` exists and is valid JSON;
- `app/`, `packages/`, `tools/`, `docs/`, and `public/` directories exist;
- core module test and source directories exist;
- package media source and test directories exist;
- the migration status ledger records the script as `migrated` after removal.

## Commit hygiene

Generated reports under `var/reports/` remain runtime artefacts and should not normally be committed.
