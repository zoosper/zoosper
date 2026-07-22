# Phase 1.37n.4.4 — Local copy profile resolver hotfix

The controlled derivative smoke exposed that `LocalMediaDerivativePathResolver::resolve()` expects a string profile key, while `LocalCopyMediaProcessor` passed the full `MediaDerivativeProfile` object.

## Outcome

```text
- Extracts profile names from MediaDerivativeProfile before calling the resolver.
- Uses profile names as derivative result keys.
- Keeps support for string profile values for transitional/simple plans.
- Adds regression coverage for resolver/profile compatibility.
```

## Verification

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorProfileCompatibilityTest.php
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
```
