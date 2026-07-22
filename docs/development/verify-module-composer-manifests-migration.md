# Verify Module Composer Manifests Migration Coverage

This document records replacement migration coverage for `tools/verify-module-composer-manifests.php`.

The source-contract intent of the legacy verify script is now preserved as durable Pest coverage.

## Current state

`tools/verify-module-composer-manifests.php` is marked `migrated` in the migration status ledger and has been retired from `tools/`.

Replacement Pest coverage lives in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyModuleComposerManifestsCoverageTest.php
```

Read-only audit tooling lives in:

```text
tools/audit-verify-module-composer-manifests-migration.php
```

## Migration result

The module composer manifests contract is now owned by Pest coverage instead of a legacy one-off verify script.

## Covered contract

The replacement coverage verifies conservative module/package composer manifest expectations useful to the 1.37w migration arc:

- root `composer.json` remains readable;
- `packages/zoosper-media/composer.json` remains present and readable;
- package composer metadata includes autoload information;
- package/module workflow signals remain present in current source;
- controlled-removal protections still recognise the remaining source-owned pilot candidate;
- the migration status ledger records the retired script as `migrated` after removal.

## Commit hygiene

Generated reports under `var/reports/` remain runtime artefacts and should not normally be committed.
