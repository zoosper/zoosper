# Media admin upload controller migration operations

Phase 1.37r.2.2 updates the migration readiness test after the temporary dump helper was removed from the repo.

The durable workflow is now:

```bash
php8.5 tools/audit-media-upload-controller-duplication.php
php8.5 tools/inspect-media-admin-upload-migration.php
```

The old temporary helper is intentionally no longer required:

```text
tools/dump-media-admin-upload-controller-1.37r2.php
```

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadControllerMigrationReadinessTest.php packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadMigrationInspectionTest.php
```

Then run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
