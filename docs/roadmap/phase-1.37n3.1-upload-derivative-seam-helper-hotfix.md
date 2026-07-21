# Phase 1.37n.3.1 — Upload derivative seam helper hotfix

The initial write-gated upload derivative seam helper used interpolated PHP strings while generating the `MediaUploadService` patch. During `--write`, PHP attempted to evaluate variables such as `$this`, `$stored`, `$errorHandler`, and `$derivatives` inside the helper process.

## Outcome

```text
- Rewrites helper patching to use preg_replace_callback.
- Emits target PHP variables literally.
- Keeps dry-run/write gating.
- Adds regression coverage for escaped generated patch variables.
```

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamToolHotfixTest.php
php8.5 packages/zoosper-media/tools/apply-upload-derivative-processing-seam.php
```
