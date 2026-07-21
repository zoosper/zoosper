# Phase 1.37n.3.2 — Upload derivative seam constructor hotfix

The upload derivative seam helper was still too brittle: it looked for an exact `?ErrorHandler $errorHandler = null` constructor ending shape. The current `MediaUploadService` constructor has evolved, so the helper could not safely add the optional `MediaUploadDerivativeDispatcher` dependency.

## Outcome

```text
- Replaces the brittle constructor regex with a small constructor parameter parser.
- Adds the derivative dispatcher dependency before the constructor close parenthesis.
- Keeps `--write` gated behaviour and backup restore on failure.
- Adds regression coverage for constructor-parser based patching.
```

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamHelperConstructorTest.php
php8.5 packages/zoosper-media/tools/apply-upload-derivative-processing-seam.php
```
