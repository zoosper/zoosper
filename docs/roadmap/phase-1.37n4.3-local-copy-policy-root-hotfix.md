# Phase 1.37n.4.3 — Local copy processor policy-root hotfix

The controlled derivative smoke exposed that `LocalCopyMediaProcessor` called `MediaProcessingPolicy::originalStorageRoot()`, but the current policy class does not expose that method.

## Outcome

```text
- Adds a local DEFAULT_ORIGINAL_STORAGE_ROOT fallback of storage/media/original.
- Uses method_exists() before calling optional policy root accessors.
- Keeps source-path validation strict.
- Adds regression coverage for the compatibility seam.
```

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorPolicyCompatibilityTest.php
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
```
