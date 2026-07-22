# Phase 1.37n.4.5 — Local copy smoke diagnostics

The controlled local copy derivative smoke can fail while full unit verification remains green because the smoke exercises runtime cooperation between the dispatcher, processor, path resolver, writer and processing result shape.

This phase improves the smoke output so a failed processing result prints:

```text
- derivative result entries
- processing errors
```

That makes the next runtime failure actionable instead of only reporting `processing result success: FAIL`.

## Verification

```bash
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyDerivativeSmokeDiagnosticsTest.php
```
