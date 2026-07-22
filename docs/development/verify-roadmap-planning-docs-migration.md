# Verify Roadmap Planning Docs Migration Coverage

This document records replacement migration coverage for `tools/verify-roadmap-planning-docs.php`.

The source-contract intent of the legacy verify script is now preserved as durable Pest coverage.

## Current state

`tools/verify-roadmap-planning-docs.php` is marked `migrated` in the migration status ledger and has been retired from `tools/`.

Replacement Pest coverage lives in:

```text
app/zoosper-core/tests/Unit/Tools/LegacyVerifyRoadmapPlanningDocsCoverageTest.php
```

Read-only audit tooling lives in:

```text
tools/audit-verify-roadmap-planning-docs-migration.php
```

## Migration result

The roadmap/planning docs contract is now owned by Pest coverage instead of a legacy one-off verify script.

## Covered contract

The replacement coverage verifies conservative roadmap/planning documentation expectations useful to the 1.37w migration arc:

- roadmap/development documentation directory exists;
- key 1.37w migration policy/status documents exist;
- the migration status ledger records the retired script as `migrated` after removal;
- generated report/audit docs remain evidence-only runtime artefacts unless intentionally promoted;
- the first pilot batch is represented as fully migrated.

## Commit hygiene

Generated reports under `var/reports/` remain runtime artefacts and should not normally be committed.
