# Media admin upload migration verification

Phase 1.37r.4 verifies the desired post-migration state for normal admin media uploads.

## Desired end state

```text
MediaAdminController::upload()
  -> MediaUploadService::upload()
    -> MediaStorage::store()
    -> MediaAssetRepository::create()
    -> MediaStoredFileCleanupService::cleanup() if persistence fails after storage
```

The normal admin upload path and the Editor.js upload path should share the same upload service.

## Migration signals

The verification audit checks that the normal admin controller:

```text
- references MediaUploadService
- calls $this->uploads->upload(...)
- no longer calls $this->storage->store(...)
- no longer calls $this->assets->create(...)
- no longer owns duplicate original-filename normalisation
```

It also checks that the upload service still delegates cleanup to `MediaStoredFileCleanupService`.
