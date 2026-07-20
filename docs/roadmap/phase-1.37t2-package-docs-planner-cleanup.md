# Phase 1.37t.2 - Package docs planner cleanup

## Goal

Return tools inventory to REVIEW 0 by removing the temporary package docs migration planner from the durable tooling path.

## Diagnosis

The package-owned documentation foundation introduced two tools:

```text
tools/audit-doc-package-ownership.php
tools/plan-package-docs-migration.php
```

The audit tool is durable and belongs in `KEEP_OPS`. The planner is an inspection/helper artefact and appeared in the tools inventory REVIEW bucket. This conflicted with the project hygiene rule of not committing temporary helper tooling unless it is intentionally classified as durable.

## Implemented

- Updated `PackageOwnedDocumentationPolicyTest` to require only the durable audit tool.
- Updated package docs migration operations to describe manual migration and removal of temporary planner artefacts.
- Documented the cleanup phase.

## Manual cleanup required

Remove the temporary planner if it exists locally:

```bash
rm -f tools/plan-package-docs-migration.php package-docs-migration-plan.txt
```

## Expected result

```text
REVIEW 0
PASS composer dump-autoload
PASS pest
PASS schema validate
PASS tools inventory
```

## Next phase

Return to Phase 1.37r.3: migrate `MediaAdminController::upload()` to `MediaUploadService` using the inspection output.
