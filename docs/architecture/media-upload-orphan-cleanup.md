# Media upload orphan cleanup

Phase 1.37r starts the media upload controller cleanup identified by Mr S.

## Problem

The media upload flow writes files to disk and then inserts metadata into the database:

```text
MediaStorage::store()
MediaAssetRepository::create()
```

If storage succeeds but the database insert fails, private/public files can be left behind without a `media_assets` row.

## Direction

Upload orchestration now lives in:

```text
Zoosper\Media\Service\MediaUploadService
```

The service centralises:

```text
- upload validation
- storage writes
- metadata persistence
- cleanup of just-written storagePath/publicPath files on persistence failure
- failure response status/message shaping
```

## Controller migration

The Editor.js upload controller now delegates persistence to `MediaUploadService` while keeping constructor compatibility with existing factories. The normal admin media upload controller should be moved to the same service once its exact constructor/view dependencies are reviewed.

## Safety policy

Cleanup is conservative:

```text
- only paths under the project base path are deleted
- public `/media/...` paths are mapped to `public/media/...`
- private relative paths are resolved under the project root
- missing files are ignored
```
