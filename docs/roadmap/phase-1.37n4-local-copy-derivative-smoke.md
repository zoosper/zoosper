# Phase 1.37n.4 — Enable local copy derivative generation in a controlled smoke path

This phase adds a controlled package-local smoke path for derivative generation.

## Outcome

```text
- Keeps upload-time derivative processing disabled by default.
- Adds a smoke tool that explicitly enables MediaUploadDerivativePolicy(true).
- Uses LocalCopyMediaProcessor via MediaUploadDerivativeDispatcher.
- Writes temporary derivative output under var/smoke/media-derivatives.
- Adds audit, tests and package-owned docs.
```

## Verification

```bash
php8.5 packages/zoosper-media/tools/audit-local-copy-derivative-smoke.php
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyDerivativeSmokeToolTest.php
PHP=php8.5 bin/verify
```
