# Media upload repository-failure cleanup operations

Run the behavioural test:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Service/MediaUploadRepositoryFailureCleanupTest.php
```

Run related media failure-path coverage:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Service/MediaUploadRepositoryFailureCleanupTest.php packages/zoosper-media/tests/Unit/Service/MediaUploadFailurePathAuditTest.php packages/zoosper-media/tests/Unit/Service/MediaStoredFileCleanupServiceTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

The test uses a temporary filesystem root and an in-memory SQLite connection, and it should not leave committed artefacts.
