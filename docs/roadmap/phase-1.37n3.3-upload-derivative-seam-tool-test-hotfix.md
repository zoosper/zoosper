# Phase 1.37n.3.3 — Upload derivative seam tool test hotfix

The constructor hotfix correctly changed the helper to pass the constructor dependency as a single-quoted literal:

```php
'private ?MediaUploadDerivativeDispatcher $derivatives = null'
```

The previous test still expected an escaped dollar sign in the helper source, which no longer matches the safer single-quoted literal. This phase updates the test expectation while keeping the source-variable interpolation guards for `$this` and `$stored`.

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamHelperConstructorTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamToolHotfixTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamTest.php
```
