# Media admin upload shared-service migration operations

Phase 1.37r.3.1 restores the durable inspection tool expected by the migration readiness tests.

Run the inspection:

```bash
php8.5 tools/inspect-media-admin-upload-migration.php
```

It writes:

```text
media-admin-upload-migration-inspection.txt
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadControllerMigrationReadinessTest.php packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadMigrationInspectionTest.php
```

Run full verification:

```bash
PHP=php8.5 bin/verify
```

If `tools/composer-php85.sh` has no executable bit after unzip, either run it through bash:

```bash
bash tools/composer-php85.sh dump-autoload
```

or restore executable mode:

```bash
chmod 755 tools/composer-php85.sh
```
