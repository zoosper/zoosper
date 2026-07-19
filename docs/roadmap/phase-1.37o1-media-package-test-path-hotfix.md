# Phase 1.37o.1 - Media package metadata test path hotfix

## Goal

Fix the package-local metadata test path used by `packages/zoosper-media/tests/Unit/Composer/PackageMetadataTest.php`.

## Diagnosis

The package readiness audit and root-level package readiness test passed, but the package-local test failed because it used:

```php
$root = dirname(__DIR__, 4);
```

From `packages/zoosper-media/tests/Unit/Composer`, four levels up resolves to `packages/`, not `packages/zoosper-media`. The test therefore read the wrong `composer.json` path and `name` was `null`.

## Implemented

- Changed the package-local test to use `dirname(__DIR__, 3)`.
- Updated operations notes with the path rule.

## Result expected

The package metadata test should now read:

```text
packages/zoosper-media/composer.json
```

and pass with `name=zoosper/media`.
