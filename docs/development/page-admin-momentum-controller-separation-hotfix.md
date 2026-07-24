# Page Momentum Controller Separation Hotfix

## Issue

The live route needs `PageMomentumAdminHttpController` because the router expects a `Zoosper\Core\Http\Response` object.

Older architecture metadata tests still expect `admin_page_momentum_routes.php` to point at the canonical renderer controller, `PageMomentumAdminController`.

The previous response-runtime fix updated too many config files, so historical metadata tests failed.

## Fix

Use two controller roles:

- Metadata/canonical renderer: `PageMomentumAdminController`
- Live runtime route: `PageMomentumAdminHttpController`

`repair-page-admin-momentum-metadata-controller.php` restores metadata files to the renderer controller and keeps live route config on the HTTP controller.

## Verification

```bash
php8.5 tools/repair-page-admin-momentum-metadata-controller.php
php8.5 tools/audit-page-admin-momentum-controller-separation.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumLiveCutoverPreflightTest.php app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumPhase146ClosureTest.php app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumPhase148ClosureTest.php app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumWiringReadinessTest.php app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumPhase159ClosureTest.php
php8.5 vendor/bin/pest
```
