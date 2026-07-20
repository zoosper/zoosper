# Media admin upload shared-service migration

Phase 1.37r.3-prep prepares the exact migration of `MediaAdminController::upload()` to `MediaUploadService`.

## Why this preparation exists

The Editor.js upload controller already delegates to `MediaUploadService`, but the normal admin upload controller may have different response semantics such as redirects, view rendering, or admin flash messages. Replacing it safely requires exact visibility into its current constructor and `upload()` method body.

## Desired end state

```text
Editor.js upload       -> MediaUploadService
Admin library upload   -> MediaUploadService
MediaUploadService     -> MediaStoredFileCleanupService on persistence failure
```

## Migration signals

The inspection tool records whether `MediaAdminController` still has:

```text
- direct storage->store calls
- direct assets->create calls
- duplicate normaliseOriginalFilename helper
- duplicate currentAdminUser helper
```

Those should disappear once the controller delegates to the shared service.
