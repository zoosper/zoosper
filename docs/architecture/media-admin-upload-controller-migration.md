# Media admin upload controller migration readiness

Phase 1.37r.2 prepares the final migration of the normal media admin upload controller to `MediaUploadService`.

## Why this is separated

`MediaEditorJsUploadController` has already been migrated to the shared upload service. The normal admin media upload path may have different view, redirect, flash-message or admin-library dependencies, so this phase adds source audit tooling before replacing the controller blindly.

## Migration target

Both upload entry points should eventually share:

```text
MediaUploadService
MediaStoredFileCleanupService
MediaUploadServiceResult
```

The desired end state is:

```text
Editor.js upload -> MediaUploadService
Admin library upload -> MediaUploadService
```

## Audit command

```bash
php8.5 tools/audit-media-upload-controller-duplication.php
```

This reports whether `MediaAdminController` still performs direct storage or repository writes.

## Source dump command

```bash
php8.5 tools/dump-media-admin-upload-controller-1.37r2.php
```

This creates a source-only dump for the exact `MediaAdminController` migration. It does not read `.env`, uploaded media or database table data.
