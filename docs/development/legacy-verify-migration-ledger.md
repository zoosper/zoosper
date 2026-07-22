# Legacy Verify Migration Ledger

This ledger tracks the controlled migration of legacy `tools/verify-*` scripts into durable Pest coverage.

Generated reports under `var/reports/` can be used as evidence, but they should normally remain uncommitted runtime artefacts.

## Current migration stages

| Stage | Purpose | Source of truth |
|---|---|---|
| Inventory | List all tool classifications | `tools/generate-tools-inventory-report.php` |
| Inspection | Inspect all legacy verify scripts | `tools/inspect-legacy-verify-migration.php` |
| Planning | Produce one-script migration plan | `tools/plan-legacy-verify-migration.php` |
| Pilot readiness | Inspect the first pilot batch | `tools/audit-legacy-verify-pilot-batch-readiness.php` |
| Migration | Add/confirm equivalent Pest coverage, then remove one script in a focused commit | Pest + git diff |

## Pilot batch

| Legacy verify script | Status | Notes |
|---|---|---|
| `tools/verify-project-structure.php` | Ready for source review | Use pilot readiness report before deletion. |
| `tools/verify-runtime-path-safety.php` | Ready for source review | Check for runtime/public path assumptions. |
| `tools/verify-service-provider-manifest-file.php` | Ready for source review | Check service provider manifest coverage. |
| `tools/verify-module-composer-manifests.php` | Ready for source review | Check composer/package manifest coverage. |
| `tools/verify-roadmap-planning-docs.php` | Ready for source review | Check roadmap/docs coverage. |

## Migration rule

A script may be deleted only when equivalent Pest coverage exists and the full test suite is green.

## Commit hygiene

Migration commits should be focused:

1. Add or confirm Pest coverage.
2. Delete only the migrated legacy verify script or scripts.
3. Regenerate reports locally.
4. Keep generated `var/reports` files uncommitted unless intentionally promoted.
