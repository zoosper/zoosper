# Phase 1.59m-z: Page Momentum Response Runtime Fix

## Issue

The live router path expects controller actions to return `Zoosper\Core\Http\Response`. The Page Momentum dashboard renderer returned a raw string, so `/admin/page-momentum` failed with a TypeError.

## Fix

- Keep `PageMomentumAdminController` as the string renderer for existing tests and dashboard composition.
- Add `PageMomentumAdminHttpController` as the live HTTP controller.
- Add `PageMomentumAdminResponseFactory` to wrap the rendered dashboard HTML into `Zoosper\Core\Http\Response`.
- Update route config to use `PageMomentumAdminHttpController`.

## Verification

```bash
php8.5 tools/fix-page-admin-momentum-response-controller.php
php8.5 tools/audit-page-admin-momentum-response-runtime.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumPhase159ClosureTest.php
php8.5 vendor/bin/pest
```
