# Phase 1.37t.1 - Media package docs test path hotfix

## Goal

Fix `MediaPackageDocsPolicyTest` so it reads package-owned docs from the media package root.

## Diagnosis

The test used:

```php
$root = dirname(__DIR__, 4);
```

From `packages/zoosper-media/tests/Unit/Documentation`, four levels up resolves to `packages/`, not `packages/zoosper-media`. The test therefore read missing/empty paths and failed to find:

```text
Zoosper Media documentation
media upload validation
```

## Implemented

- Changed the test root to `dirname(__DIR__, 3)`.
- Updated package-docs migration operations with the correct path rule.

## Expected result

The package-owned documentation policy tests should pass.
