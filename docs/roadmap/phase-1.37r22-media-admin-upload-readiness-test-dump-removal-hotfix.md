# Phase 1.37r.2.2 - Media admin upload readiness test dump-removal hotfix

## Goal

Fix the media admin upload readiness test after the temporary dump helper was removed before commit.

## Diagnosis

The repo correctly removed:

```text
tools/dump-media-admin-upload-controller-1.37r2.php
```

because it was a one-off dump helper. However, `MediaAdminUploadControllerMigrationReadinessTest` still attempted to read that file and assert its source contents, causing Pest to fail.

## Implemented

- Updated `MediaAdminUploadControllerMigrationReadinessTest` to check the durable inspection tool instead:

```text
tools/inspect-media-admin-upload-migration.php
```

- Kept the audit-tool assertions intact.
- Updated operations documentation to use the audit + inspection tools only.

## Expected result

The targeted controller migration readiness tests and full verification should pass.
