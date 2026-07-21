# Media upload repository-failure cleanup

Phase 1.37r.7 adds a concrete behavioural test for the media upload failure path.

## Behaviour under test

```text
1. MediaUploadService validates a real PNG fixture.
2. MediaStorage writes private/public files under a temporary project root.
3. MediaAssetRepository attempts to persist metadata against an SQLite connection without the media table.
4. Repository persistence fails after storage succeeds.
5. MediaUploadService catches the failure and delegates cleanup.
6. MediaStoredFileCleanupService removes the private/public files.
7. MediaUploadService returns a 500 failure result.
```

## Why this matters

The previous phases proved the source shape and audit contracts. This phase proves the actual runtime failure path with concrete classes and a temporary filesystem root.
