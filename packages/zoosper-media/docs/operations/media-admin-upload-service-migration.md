# Media admin upload service migration operations

Phase 1.37r.3.2 fixes the package-local migration tool test path.

From:

```text
packages/zoosper-media/tests/Unit/Controller
```

three levels up resolves to:

```text
packages/zoosper-media
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadServiceMigrationToolTest.php packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadControllerMigrationReadinessTest.php packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadMigrationInspectionTest.php
```

Dry-run migration:

```bash
php8.5 packages/zoosper-media/tools/apply-admin-upload-service-migration.php
```

Apply only after reviewing dry-run output:

```bash
php8.5 packages/zoosper-media/tools/apply-admin-upload-service-migration.php --write
```

Remove backup before commit after verifying the migration:

```bash
rm -f packages/zoosper-media/src/Controller/MediaAdminController.php.phase137r3.bak
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
