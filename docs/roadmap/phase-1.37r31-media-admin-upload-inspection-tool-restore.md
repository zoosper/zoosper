# Phase 1.37r.3.1 - Media admin upload inspection tool restore

## Goal

Restore the durable media admin upload inspection tool required by the migration readiness tests.

## Diagnosis

`MediaAdminUploadControllerMigrationReadinessTest` and `MediaAdminUploadMigrationInspectionTest` read:

```text
tools/inspect-media-admin-upload-migration.php
```

The failing Pest output showed the source read as empty, which means the tool was missing or not present in the working tree after applying the later PHP 8.5 toolchain phase.

## Implemented

- Restored `tools/inspect-media-admin-upload-migration.php`.
- Kept it source-only: it does not read `.env`, uploaded media, secrets, or database table data.
- Updated operations documentation with the targeted test command.

## Expected result

The two media admin upload migration inspection tests should pass.
