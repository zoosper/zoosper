# Verify Service Provider Manifest Migration Coverage

This document records replacement migration coverage for `tools/verify-service-provider-manifest-file.php`.

The goal is to preserve the source-contract intent of the legacy verify script as durable Pest coverage before any deletion happens.

## Current state

`tools/verify-service-provider-manifest-file.php` remains `source-owned` in the migration status ledger.

This phase adds replacement Pest coverage in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyServiceProviderManifestCoverageTest.php
```

It also adds a read-only audit tool:

```text
tools/audit-verify-service-provider-manifest-migration.php
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

The replacement coverage verifies conservative service provider manifest expectations useful to the 1.37w migration arc:

- service-provider manifest tooling remains present;
- application factory / service-provider references remain source-discoverable;
- service provider manifest strings remain part of source-level checks;
- the candidate script remains source-owned until ledger promotion;
- the controlled removal helper still recognises the candidate but refuses deletion while source-owned.
