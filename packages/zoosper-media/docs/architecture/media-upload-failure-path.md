# Media upload failure path

Phase 1.37r.5 locks the storage-succeeds / persistence-fails contract for media uploads.

## Required sequence

```text
1. Validate upload.
2. Store private and public files.
3. Persist media asset metadata.
4. If persistence throws after storage succeeds, clean the just-written files.
5. Return a 500 upload failure response.
```

## Shared service requirement

Both upload entry points should delegate to the shared upload service:

```text
MediaAdminController::upload()
MediaEditorJsUploadController::upload()
```

The shared service owns the failure path:

```text
MediaUploadService
  -> MediaStoredFileCleanupService
```

## Safety requirement

Cleanup must only delete files that resolve under the configured project base path. Public `/media/...` URLs map to `public/media/...`.
