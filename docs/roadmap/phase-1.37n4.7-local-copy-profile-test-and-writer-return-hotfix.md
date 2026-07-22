# Phase 1.37n.4.7 — Local copy profile test and writer-return hotfix

The controlled smoke was creating derivative files, but the test for the deterministic fallback profile key used a double-quoted expectation, so `$index` was interpolated by the test itself. The smoke also showed blank derivative result entries because `LocalMediaDerivativeWriter::write()` can return `null`/void while the processor tried to read `$written->publicPath`.

## Outcome

```text
- Fixes the profile fallback test expectation to keep `$index` literal.
- Adds `publicDerivativePath()` to use writer-return publicPath when available.
- Falls back to the resolved target publicPath when the writer returns void/null.
- Removes the smoke warning caused by reading `publicPath` on null.
```

## Verification

```bash
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorProfileCompatibilityTest.php
```
