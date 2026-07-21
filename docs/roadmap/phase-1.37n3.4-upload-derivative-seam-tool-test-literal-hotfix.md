# Phase 1.37n.3.4 — Upload derivative seam tool test literal hotfix

The helper source correctly uses a single-quoted literal for the constructor dependency:

```php
'private ?MediaUploadDerivativeDispatcher $derivatives = null'
```

The previous regression test used a double-quoted PHP string around that expected text, causing `$derivatives` to be interpolated by the test itself. This phase changes the expectation to a single-quoted test literal so `$derivatives` remains literal text.

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamHelperConstructorTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamToolHotfixTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamTest.php
```
