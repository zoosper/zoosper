# Verify Roadmap Planning Docs Migration Coverage

This document records replacement migration coverage for `tools/verify-roadmap-planning-docs.php`.

The goal is to preserve the source-contract intent of the legacy verify script as durable Pest coverage before any deletion happens.

## Current state

`tools/verify-roadmap-planning-docs.php` remains `source-owned` in the migration status ledger.

This phase adds replacement Pest coverage in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyRoadmapPlanningDocsCoverageTest.php
```

It also adds a read-only audit tool:

```text
tools/audit-verify-roadmap-planning-docs-migration.php
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

The replacement coverage verifies conservative roadmap/planning documentation expectations useful to the 1.37w migration arc:

- roadmap/development documentation directory exists;
- key 1.37w migration policy/status documents exist;
- the migration status ledger includes the final source-owned pilot candidate;
- generated report/audit docs are evidence-only runtime artefacts unless intentionally promoted;
- the controlled removal helper recognises the final pilot candidate but refuses deletion while source-owned.
