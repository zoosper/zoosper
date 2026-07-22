# Local copy derivative smoke

Phase 1.37n.4 adds a controlled smoke path for the upload derivative seam.

The production policy remains disabled by default. The smoke tool explicitly enables `MediaUploadDerivativePolicy(true)` and runs the derivative dispatcher against a temporary local fixture beneath:

```text
var/smoke/media-derivatives
```

This verifies that the dispatcher, local copy processor, derivative resolver and writer can cooperate without introducing upload-time behaviour by default.

The smoke tool is intentionally package-owned under `packages/zoosper-media/tools/` so future standalone package users can validate the same behaviour outside the root CMS repository.
