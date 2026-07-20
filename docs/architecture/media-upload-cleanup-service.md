# Media upload cleanup service

Phase 1.37r.1 extracts orphan-file deletion into a dedicated, behaviour-testable service.

## Why

Phase 1.37r centralised upload orchestration in `MediaUploadService`. This follow-up makes the cleanup portion independently testable instead of hiding it inside the upload service.

## New service

```text
Zoosper\Media\Service\MediaStoredFileCleanupService
```

The service removes files written during a failed upload while applying strict path safety rules.

## Safety rules

```text
- Only delete resolved files under the configured project base path.
- Map public /media/... URLs to public/media/... files.
- Resolve private relative paths under the project root.
- Ignore missing paths.
- Return deleted/skipped counts for logging and diagnostics.
```

## Upload orchestration

`MediaUploadService` now delegates cleanup to the cleanup service and logs:

```text
cleanup_attempted
cleanup_deleted
cleanup_skipped
```

This improves observability for the storage-succeeds / DB-fails failure path.
