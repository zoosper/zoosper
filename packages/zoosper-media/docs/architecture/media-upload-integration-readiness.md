# Media upload integration readiness

Phase 1.37r.6.1 cleans the readiness probe and prepares the concrete-fixture strategy for the next behavioural test.

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

## Probe result interpretation

The probe reports whether `MediaStorage`, `MediaAssetRepository` and `MediaUploadValidator` can be substituted in a clean test. If they are final or otherwise unsuitable for fake subclasses, the behavioural test should use concrete fixtures.

## Preferred next strategy

Use:

```text
- a temporary filesystem root
- a concrete MediaUploadValidator
- a concrete MediaStorage writing under the temp root
- a repository fixture that fails after storage succeeds
- assertions that private and public files no longer exist after failure
```

If a concrete repository fixture needs database support, prefer a SQLite-backed repository fixture over brittle reflection.

## Probe command

```bash
php8.5 packages/zoosper-media/tools/probe-media-upload-integration-readiness.php
```

The probe should run without PHP warnings. Earlier non-compound `use ReflectionClass;` and `use ReflectionException;` imports were intentionally removed.
