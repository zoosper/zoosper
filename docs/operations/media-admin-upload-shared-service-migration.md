# Media admin upload shared-service migration operations

Run the inspection:

```bash
php8.5 tools/inspect-media-admin-upload-migration.php
```

It writes:

```text
media-admin-upload-migration-inspection.txt
```

Run the test:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadMigrationInspectionTest.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

The next implementation phase should use the inspection output to migrate `MediaAdminController::upload()` without guessing redirect, flash or view behaviour.
