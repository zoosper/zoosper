# Verify Module Composer Manifests Migration Coverage

This document records replacement migration coverage for `tools/verify-module-composer-manifests.php`.

The goal is to preserve the source-contract intent of the legacy verify script as durable Pest coverage before any deletion happens.

## Current state

`tools/verify-module-composer-manifests.php` remains `source-owned` in the migration status ledger.

This phase adds replacement Pest coverage in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyModuleComposerManifestsCoverageTest.php
```

It also adds a read-only audit tool:

```text
tools/audit-verify-module-composer-manifests-migration.php
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

The replacement coverage verifies conservative module/package composer manifest expectations useful to the 1.37w migration arc:

- root `composer.json` remains readable;
- `packages/zoosper-media/composer.json` remains present and readable;
- package composer metadata includes autoload information;
- package/module scaffolding tests remain present;
- the candidate script remains source-owned until ledger promotion;
- the controlled removal helper still recognises the candidate but refuses deletion while source-owned.
