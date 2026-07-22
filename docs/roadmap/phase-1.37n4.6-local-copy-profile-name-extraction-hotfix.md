# Phase 1.37n.4.6 — Local copy profile-name extraction hotfix

The controlled derivative smoke revealed that current `MediaDerivativeProfile` values did not expose a public `name` property or `name()` method. The processor therefore failed with:

```text
Media derivative profile does not expose a name.
```

## Outcome

```text
- Adds a robust profileName() extractor.
- Checks common accessors such as name, key, code, handle, profile, id and getName().
- Uses reflection for non-public profile name properties.
- Falls back to string array keys or deterministic profile-N names.
- Keeps LocalMediaDerivativePathResolver receiving a string key.
```

## Verification

```bash
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorProfileCompatibilityTest.php
```
