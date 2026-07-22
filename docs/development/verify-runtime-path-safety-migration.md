# Verify Runtime Path Safety Migration Coverage

This document records replacement migration coverage for `tools/verify-runtime-path-safety.php`.

The source-contract intent of the legacy verify script is now preserved as durable Pest coverage.

## Current state

`tools/verify-runtime-path-safety.php` is marked `migrated` in the migration status ledger and has been retired from `tools/`.

Replacement Pest coverage lives in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyRuntimePathSafetyCoverageTest.php
```

Read-only audit tooling lives in:

```text
tools/audit-verify-runtime-path-safety-migration.php
```

## Migration result

The runtime path safety contract is now owned by Pest coverage instead of a legacy one-off verify script.

## Covered contract

The replacement coverage verifies conservative runtime/path expectations that are useful to the 1.37w migration arc:

- trusted runtime directories exist or can be represented without requiring committed runtime files;
- generated report paths remain under `var/reports`;
- public/runtime boundary policy tooling remains present;
- path traversal and operational-tool deletion protections remain covered by the removal workflow;
- the migration status ledger records the script as `migrated` after removal.

## Commit hygiene

Generated reports under `var/reports/` remain runtime artefacts and should not normally be committed.
