# Verify Service Provider Manifest Migration Coverage

This document records replacement migration coverage for `tools/verify-service-provider-manifest-file.php`.

The source-contract intent of the legacy verify script is now preserved as durable Pest coverage.

## Current state

`tools/verify-service-provider-manifest-file.php` is marked `migrated` in the migration status ledger and has been retired from `tools/`.

Replacement Pest coverage lives in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyServiceProviderManifestCoverageTest.php
```

Read-only audit tooling lives in:

```text
tools/audit-verify-service-provider-manifest-migration.php
```

## Migration result

The service provider manifest contract is now owned by Pest coverage instead of a legacy one-off verify script.

## Covered contract

The replacement coverage verifies conservative service provider manifest expectations useful to the 1.37w migration arc:

- service-provider manifest tooling remains source-discoverable;
- service provider related source signals remain present;
- controlled-removal protections still recognise remaining source-owned pilot candidates;
- the migration status ledger records the retired script as `migrated` after removal.

## Commit hygiene

Generated reports under `var/reports/` remain runtime artefacts and should not normally be committed.
