# Media upload orphan cleanup operations

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/Service/MediaUploadServiceCleanupContractTest.php packages/zoosper-media/tests/Unit/Controller/MediaEditorJsUploadControllerCleanupTest.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Browser smoke after apply:

```text
/admin/pages/create
```

Expected:

```text
- Editor.js image upload still returns success JSON.
- Page save still works.
- Frontend image still renders.
```

If a database insert fails after storage, `MediaUploadService` attempts to remove the just-written private/public files before returning a failure response.
