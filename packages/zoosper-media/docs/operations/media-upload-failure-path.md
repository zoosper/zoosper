# Media upload failure path operations

Phase 1.37r.5.3 fixes the remaining Pest warning by ensuring the regression test itself keeps `$storedPath` as literal text.

Run the failure-path audit:

```bash
php8.5 packages/zoosper-media/tools/audit-media-upload-failure-path.php
```

Expected result:

```text
Result: OK
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Service/MediaUploadFailurePathAuditTest.php packages/zoosper-media/tests/Unit/Service/MediaStoredFileCleanupServiceTest.php packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadServiceMigrationAuditTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
