# Media admin upload service migration

The normal media admin upload path should use the same upload orchestration as Editor.js uploads:

```text
MediaAdminController::upload()
  -> MediaUploadService::upload()
    -> MediaStorage::store()
    -> MediaAssetRepository::create()
    -> MediaStoredFileCleanupService::cleanup() on persistence failure
```

## Why use a migration helper

`MediaAdminController::upload()` may contain existing redirect or response behaviour that should be preserved. The helper inspects the current method and performs a conservative transformation only when it recognises the direct storage/repository persistence shape.

## End state

The controller should no longer duplicate:

```text
- validation orchestration
- storage writes
- metadata persistence
- original filename normalisation
- orphan-file cleanup responsibility
```

Those belong to `MediaUploadService`.
