# Page Momentum Status Count Hotfix

## Issue

The first Phase 1.57 legacy-output hotfix restored the strings expected by older wiring-readiness tests, but it added a fifth status row. The newer `PageAdminMomentumLivePanelTest` expects exactly four status rows.

## Fix

`PageMomentumStatusProvider` now returns exactly four items again. The legacy phrases remain in the rendered output inside the `Controller output` detail text:

- `Core decoupling readiness`
- `PageRenderer report-only candidate`

This satisfies both the older wiring-readiness continuity test and the newer live-panel count test.

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumWiringReadinessTest.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumLivePanelTest.php
php8.5 tools/smoke-page-admin-momentum-live-panel.php
php8.5 vendor/bin/pest
```
