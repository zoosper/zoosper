# Phase 1.37r.6 - Media upload integration readiness probe

## Goal

Prepare the next true behavioural test for the media upload failure path without guessing whether concrete media classes are final or safely substitutable.

## Implemented

- Added `packages/zoosper-media/tools/probe-media-upload-integration-readiness.php`.
- Added `MediaUploadIntegrationReadinessProbeTest`.
- Added package-owned architecture and operations docs.
- Added root operations link doc.

## Why this matters

The source-level audit now passes, but a true integration-style test needs to know whether it can safely fake or subclass:

```text
MediaStorage
MediaAssetRepository
MediaUploadValidator
```

If those classes are final or not substitutable, the next phase should use concrete fixtures such as a temporary filesystem root and SQLite-backed repository.

## Next phase

Phase 1.37r.7 should implement the actual storage-succeeds / repository-fails behavioural test using the strategy indicated by the probe output.
