# Phase 1.37n.3 — Connect media derivative processing to upload flow behind a feature/policy seam

This phase adds a feature-gated upload derivative processing seam.

## Outcome

```text
- Adds MediaUploadDerivativePolicy.
- Adds MediaUploadDerivativeDispatcher.
- Keeps upload-time derivative generation disabled by default.
- Adds a write-gated helper to connect MediaUploadService to derivative dispatching.
- Adds package-local audit, tests and docs.
```

## Verification

```bash
php8.5 packages/zoosper-media/tools/audit-upload-derivative-processing-seam.php
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamTest.php
PHP=php8.5 bin/verify
```
