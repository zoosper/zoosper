# Phase 1.48 Preflight State Hotfix

## Issue

`PageMomentumLiveCutoverPreflight` still treated `page_momentum_routes.enabled=false` and `page_momentum_menu.enabled=false` as required checks, but Phase 1.48m-z intentionally activated those metadata flags.

That made `readyForManualCutover` false after activation even though the metadata was structurally valid.

## Fix

The preflight now validates that the enabled flags are safe booleans and that route/menu/controller/permission metadata is internally consistent. It no longer requires the metadata to be disabled.

The service still performs no live mutation.

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumLiveCutoverPreflightTest.php
php8.5 tools/audit-page-admin-momentum-live-cutover-preflight.php
php8.5 tools/generate-page-admin-momentum-cutover-preview.php
php8.5 tools/audit-page-admin-momentum-phase-148-readiness.php
php8.5 vendor/bin/pest
```
