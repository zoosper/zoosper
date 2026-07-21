# Upload derivative processing seam operations

Run the audit:

```bash
php8.5 packages/zoosper-media/tools/audit-upload-derivative-processing-seam.php
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamToolHotfixTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamHelperConstructorTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorAuditTest.php
```

Dry-run the upload-service migration helper:

```bash
php8.5 packages/zoosper-media/tools/apply-upload-derivative-processing-seam.php
```

Apply only after reviewing dry-run output:

```bash
php8.5 packages/zoosper-media/tools/apply-upload-derivative-processing-seam.php --write
rm -f packages/zoosper-media/src/Service/MediaUploadService.php.phase137n3.bak
```

The helper uses callback-based patch replacements and constructor parsing so target PHP variables such as `$this`, `$stored`, `$errorHandler`, and `$derivatives` are emitted literally and the constructor dependency can be inserted even when `MediaUploadService` changes dependency order.

Then verify from the root project:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
