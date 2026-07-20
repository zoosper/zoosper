# Phase 1.37r.1.1 - Media upload cleanup test hotfix

## Goal

Update the media upload cleanup contract tests to match the extracted cleanup-service architecture.

## Diagnosis

The cleanup extraction itself was correct: `MediaStoredFileCleanupServiceTest` passed, and the service deleted private/public files safely. The failing tests were still expecting the earlier inline helper names from Phase 1.37r:

```text
cleanupStoredFiles
safeUnlink
```

After Phase 1.37r.1, cleanup is intentionally delegated to `MediaStoredFileCleanupService`, so the tests must assert that delegation instead of the removed private helper names.

## Implemented

- Updated `MediaUploadServiceCleanupExtractionTest` to assert the short class name used in the same namespace.
- Updated `MediaUploadServiceCleanupContractTest` to assert delegation to `$this->cleanup->cleanup($stored)`.
- Kept result and service-registration assertions intact.

## Expected result

All media upload cleanup targeted tests should pass.
