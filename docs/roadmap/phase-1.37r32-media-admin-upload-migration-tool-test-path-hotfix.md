# Phase 1.37r.3.2 - Media admin upload migration tool test path hotfix

## Goal

Fix `MediaAdminUploadServiceMigrationToolTest` so it reads the package-local migration tool from the media package root.

## Diagnosis

The migration tool itself worked in the real dry run:

```text
php8.5 packages/zoosper-media/tools/apply-admin-upload-service-migration.php
```

It correctly detected direct storage/repository calls and printed the expected dry-run message. The failing tests used:

```php
$root = dirname(__DIR__, 4);
```

From `packages/zoosper-media/tests/Unit/Controller`, four levels up resolves to `packages/`, not `packages/zoosper-media`. The test therefore read the wrong tool path and got empty content.

## Implemented

- Changed the test root to `dirname(__DIR__, 3)`.
- Updated package-owned migration operations with the correct path rule.

## Expected result

The targeted migration tool tests should pass.
