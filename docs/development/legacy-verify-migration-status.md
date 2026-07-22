# Legacy Verify Migration Status

This document is the durable status ledger for controlled migration of legacy `tools/verify-*` scripts into Pest tests.

The status ledger exists because a script should not be deleted while tests still expect it to be source-owned.

## Status values

### source-owned

The legacy verify script is still present in `tools/` and remains part of the migration backlog.

### migrated

The legacy verify script has equivalent Pest coverage and may be removed from `tools/` in a focused migration commit.

## Pilot batch status

| Legacy verify script | Status | Expected Pest ownership | Notes |
|---|---|---|---|
| `tools/verify-project-structure.php` | source-owned | `app/zoosper-core/tests/Unit/Tools` or existing project-structure tests | Do not delete until status is changed to `migrated` in the same focused phase. |
| `tools/verify-runtime-path-safety.php` | source-owned | runtime path safety tests / public webroot policy tests | Do not delete until equivalent coverage is confirmed. |
| `tools/verify-service-provider-manifest-file.php` | source-owned | service provider manifest/config tests | Do not delete until equivalent coverage is confirmed. |
| `tools/verify-module-composer-manifests.php` | source-owned | module composer manifest/package identity tests | Do not delete until equivalent coverage is confirmed. |
| `tools/verify-roadmap-planning-docs.php` | source-owned | documentation/roadmap tests | Do not delete until equivalent coverage is confirmed. |

## Migration process

1. Inspect the legacy verify script.
2. Add or confirm equivalent Pest coverage.
3. Change this ledger status from `source-owned` to `migrated` for that script.
4. Remove the script in the same focused phase.
5. Run the full Pest suite.
6. Regenerate local reports.
7. Keep generated `var/reports` artefacts uncommitted unless intentionally promoted.

## Safety note

`--apply` deletion should only be used after this ledger has been updated and the corresponding tests understand the script is migrated.
