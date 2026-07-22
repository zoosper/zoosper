# Verify Runtime Path Safety Migration Coverage

This document records replacement migration coverage for `tools/verify-runtime-path-safety.php`.

The goal is to preserve the source-contract intent of the legacy verify script as durable Pest coverage before any deletion happens.

## Current state

`tools/verify-runtime-path-safety.php` remains `source-owned` in the migration status ledger.

This phase adds replacement Pest coverage in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyRuntimePathSafetyCoverageTest.php
```

It also adds a read-only audit tool:

```text
tools/audit-verify-runtime-path-safety-migration.php
```

## Migration gate

The legacy script must not be deleted in this phase.

A future focused phase may delete it only after:

1. the replacement Pest coverage is green;
2. `docs/development/legacy-verify-migration-status.md` changes the script status from `source-owned` to `migrated`;
3. `tools/remove-migrated-legacy-verify.php` allows deletion because the ledger says `migrated`;
4. the full Pest suite remains green;
5. generated `var/reports` artefacts remain uncommitted unless intentionally promoted.

## Covered contract

The replacement coverage verifies conservative runtime/path expectations that are useful to the 1.37w migration arc:

- trusted runtime directories exist or can be represented without requiring committed runtime files;
- generated report paths remain under `var/reports`;
- public/runtime boundary policy tooling remains present;
- path traversal and operational-tool deletion protections remain covered by the removal workflow;
- the candidate script is still source-owned until ledger promotion.
