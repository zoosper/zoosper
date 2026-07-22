# Verify Project Structure Migration Coverage

This document records the migration coverage for `tools/verify-project-structure.php`.

The goal is to preserve the source-contract intent of the legacy verify script as durable Pest coverage before any deletion happens.

## Current state

`tools/verify-project-structure.php` remains source-owned in the migration status ledger.

This phase adds replacement Pest coverage in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyProjectStructureCoverageTest.php
```

It also adds a read-only audit tool:

```text
tools/audit-verify-project-structure-migration.php
```

## Migration gate

The legacy script must not be deleted in this phase.

A future focused phase may delete it only after:

1. the replacement Pest coverage is green;
2. `docs/development/legacy-verify-migration-status.md` changes the status from `source-owned` to `migrated`;
3. `tools/remove-migrated-legacy-verify.php` allows the deletion because the ledger says `migrated`;
4. the full Pest suite remains green;
5. generated `var/reports` artefacts remain uncommitted unless intentionally promoted.

## Covered contract

The replacement Pest coverage verifies the stable repository structure needed by the tooling and modular architecture:

- root `composer.json` exists and is valid JSON;
- `app/`, `packages/`, `tools/`, `docs/`, and `public/` directories exist;
- core module test and source directories exist;
- package media source and test directories exist;
- project tooling needed by the 1.37w migration arc exists.

This is intentionally conservative. It does not attempt to prove every historical detail in the old script until the old script has been reviewed directly.
