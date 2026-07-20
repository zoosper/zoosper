# Media upload cleanup service operations

Phase 1.37r.1.2 fixes the remaining cleanup contract test assertion.

The previous assertion tried to use Pest's expectation chaining as a fallback:

```php
expect($source)->toContain(MediaStoredFileCleanupService::class)
    ->or($source)->toContain('MediaStoredFileCleanupService');
```

The first `toContain()` fails immediately before the fallback is useful. Since `MediaUploadService` is in the same namespace as `MediaStoredFileCleanupService`, the source legitimately contains the short class name, not the fully qualified class string.

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/Service/MediaStoredFileCleanupServiceTest.php packages/zoosper-media/tests/Unit/Service/MediaUploadServiceCleanupExtractionTest.php packages/zoosper-media/tests/Unit/Service/MediaUploadServiceCleanupContractTest.php packages/zoosper-media/tests/Unit/Controller/MediaEditorJsUploadControllerCleanupTest.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
