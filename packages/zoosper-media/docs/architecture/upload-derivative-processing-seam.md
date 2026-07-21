# Upload derivative processing seam

Phase 1.37n.3 adds an upload-time derivative processing seam without forcing derivative generation on by default.

## Components

```text
MediaUploadDerivativePolicy
MediaUploadDerivativeDispatcher
LocalCopyMediaProcessor
```

The policy is disabled by default. When disabled, the dispatcher returns a successful empty derivative result and the upload remains unchanged.

When enabled later, the dispatcher delegates to `MediaProcessorInterface`, initially backed by `LocalCopyMediaProcessor`, and later by optional engine packages such as `zoosper/media-gd` or `zoosper/media-imagick`.

## Safety

Derivative processing should not fail the upload response while this feature is developing. Upload persistence remains the source of truth; derivatives are cache artefacts.
