# Phase 1.37n.4.2 — Local copy processor docblock hotfix

The interface hotfix correctly made `LocalCopyMediaProcessor` compatible with `MediaProcessorInterface`, but the audit test still locked the explicit phrase:

```text
bytes are deliberately unchanged
```

The implementation remains a no-op local copy processor. This hotfix restores that exact safety phrase in the processor docblock and the transitional `processStoragePath()` helper documentation.

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorInterfaceCompatibilityTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyDerivativeSmokeToolTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorAuditTest.php
```
