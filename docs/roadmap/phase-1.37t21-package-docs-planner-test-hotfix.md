# Phase 1.37t.2.1 - Package docs planner test hotfix

## Goal

Fix the package-owned documentation policy test after the operations guide intentionally mentioned the temporary planner removal command.

## Diagnosis

The test asserted that `docs/operations/package-docs-migration.md` must not contain:

```text
plan-package-docs-migration.php
```

That was too strict because the operations guide correctly tells developers to remove the temporary planner before committing:

```bash
rm -f tools/plan-package-docs-migration.php package-docs-migration-plan.txt
```

So the implementation guidance was correct; the test assertion was wrong.

## Implemented

- Updated the test to assert that the planner is treated as a removable helper rather than forbidding mention of its filename.
- Kept the operations guide explicit so developers can clean the exact temporary files and restore tools inventory to REVIEW 0.

## Expected result

The documentation policy tests and full verification should pass once the temporary planner file is removed locally.
