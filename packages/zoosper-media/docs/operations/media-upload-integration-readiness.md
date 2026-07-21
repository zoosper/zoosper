# Media upload integration readiness operations

Run the readiness probe:

```bash
php8.5 packages/zoosper-media/tools/probe-media-upload-integration-readiness.php
```

Expected result:

```text
Result: OK
```

The probe should not emit PHP warnings about non-compound Reflection imports.

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Service/MediaUploadIntegrationReadinessProbeTest.php packages/zoosper-media/tests/Unit/Service/MediaUploadFailurePathAuditTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

Do not commit generated temporary media files or probe output snapshots.
