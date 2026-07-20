# Media admin upload migration verification operations

After applying the Phase 1.37r.3 migration helper with `--write`, run:

```bash
php8.5 packages/zoosper-media/tools/audit-admin-upload-service-migration.php
```

Expected result after the controller is migrated:

```text
Result: OK
```

If the audit reports direct storage or asset calls, rerun the migration inspection and review the controller manually:

```bash
php8.5 tools/inspect-media-admin-upload-migration.php
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadServiceMigrationAuditTest.php packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadServiceMigrationToolTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
