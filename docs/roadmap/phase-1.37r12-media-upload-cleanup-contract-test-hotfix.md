# Phase 1.37r.1.2 - Media upload cleanup contract test hotfix

## Goal

Fix the final stale assertion in `MediaUploadServiceCleanupContractTest`.

## Diagnosis

`MediaUploadService` lives in the same namespace as `MediaStoredFileCleanupService`, so the source uses the short class name:

```php
private MediaStoredFileCleanupService $cleanup;
```

The test incorrectly looked first for the fully qualified class string:

```text
Zoosper\Media\Service\MediaStoredFileCleanupService
```

and failed before the intended short-name fallback could succeed.

## Implemented

- Assert `class_exists(MediaStoredFileCleanupService::class)` separately.
- Assert that the source contains the short class name `MediaStoredFileCleanupService`.
- Keep the storage, DB persistence and cleanup delegation assertions intact.

## Expected result

All targeted media upload cleanup tests should pass.
