# Local copy media processor operations

Run the package-local audit:

```bash
php8.5 packages/zoosper-media/tools/audit-local-copy-media-processor.php
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorAuditTest.php packages/zoosper-media/tests/Unit/Processing/LocalMediaDerivativePathResolverTest.php packages/zoosper-media/tests/Unit/Processing/MediaProcessingPolicyTest.php
```

Run full verification from the root project:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

This phase intentionally does not change upload-time behaviour. It adds the first concrete processor adapter so future phases can connect derivative generation deliberately.
