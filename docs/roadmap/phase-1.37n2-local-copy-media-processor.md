# Phase 1.37n.2 — Engine-free local copy/no-op media processor adapter

This phase adds an engine-free `LocalCopyMediaProcessor` behind `MediaProcessorInterface`.

## Outcome

```text
- Adds a concrete local processor adapter.
- Copies originals into derivative slots without resizing or re-encoding.
- Preserves immutable-original policy.
- Keeps GD/Imagick decisions deferred to optional future packages.
- Adds package-local audit and tests.
```

## Verification

```bash
php8.5 packages/zoosper-media/tools/audit-local-copy-media-processor.php
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorAuditTest.php
PHP=php8.5 bin/verify
```
