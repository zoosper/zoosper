# Media upload integration readiness

Phase 1.37r.6 prepares the next behavioural test for the media upload failure path.

## Target behaviour

The next integration-style test should prove the actual runtime path:

```text
storage succeeds / repository fails
```

Expected outcome:

```text
- MediaUploadService catches the repository failure.
- MediaStoredFileCleanupService removes private/public files written by storage.
- MediaUploadService returns a 500 failure result.
- No orphan media file remains.
```

## Why a readiness probe first

The concrete media classes may be final or may require real dependencies such as PDO or filesystem roots. The readiness probe reports whether `MediaStorage`, `MediaAssetRepository` and `MediaUploadValidator` can be substituted in a clean unit/integration test or whether the test should use concrete fixtures.

## Probe command

```bash
php8.5 packages/zoosper-media/tools/probe-media-upload-integration-readiness.php
```

The output should guide whether Phase 1.37r.7 uses subclasses/fakes or a real SQLite-backed fixture.
