# Media admin upload controller migration operations

Phase 1.37r.2.1 fixes the readiness test assertion for the dump tool. The dump tool is allowed to mention `.env` in its safety note, because that is how it documents that `.env` is not read. The test now checks that `.env` is not present as a quoted target path.

Run readiness audit:

```bash
php8.5 tools/audit-media-upload-controller-duplication.php
```

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/Controller/MediaAdminUploadControllerMigrationReadinessTest.php
```

If the audit reports that `MediaAdminController` still has direct storage/assets calls, run:

```bash
php8.5 tools/dump-media-admin-upload-controller-1.37r2.php
```

Then inspect or share:

```text
media-admin-upload-controller-1.37r2-dump.txt
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
