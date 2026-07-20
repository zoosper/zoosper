# Phase 1.37r.1 - Media upload cleanup service extraction

## Goal

Make orphan-file cleanup independently testable and observable.

## Implemented

- Added `MediaStoredFileCleanupService`.
- Added `MediaStoredFileCleanupResult`.
- Updated `MediaUploadService` to delegate cleanup to the cleanup service.
- Registered the cleanup service in media services.
- Added behaviour tests for private/public deletion, outside-root safety and public `/media/...` path mapping.
- Added contract tests proving the upload service uses the cleanup service and logs cleanup counts.

## Why this moves fast safely

This phase hardens the most dangerous part of the media upload failure path without needing to rewrite every media controller in the same step.

## Next phase options

- Phase 1.37r.2: migrate normal admin media upload controller to `MediaUploadService`.
- Phase 1.37r.3: add behaviour-level controller tests with fake repository failure paths.
- Phase 1.37n.1: local derivative processor behind `MediaProcessorInterface`.
