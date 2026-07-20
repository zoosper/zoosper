# Phase 1.37r.3 - Media admin upload service migration helper

## Goal

Move aggressively toward migrating `MediaAdminController::upload()` to `MediaUploadService` without blindly overwriting the controller source.

## Implemented

- Added package-local migration helper:

```text
packages/zoosper-media/tools/apply-admin-upload-service-migration.php
```

- The helper is dry-run by default and requires `--write` to modify source.
- It verifies the current upload method shape before changing anything.
- It writes a backup before modifying `MediaAdminController.php`.
- Added tests for write gating, fail-safe signals and service-targeted migration intent.
- Added package-owned architecture and operations docs.

## Manual apply path

```bash
php8.5 packages/zoosper-media/tools/apply-admin-upload-service-migration.php
php8.5 packages/zoosper-media/tools/apply-admin-upload-service-migration.php --write
rm -f packages/zoosper-media/src/Controller/MediaAdminController.php.phase137r3.bak
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Next phase

If the helper recognises and migrates the controller successfully, Phase 1.37r.4 should add behaviour-level tests proving normal admin upload shares the same orphan cleanup semantics as Editor.js uploads.
