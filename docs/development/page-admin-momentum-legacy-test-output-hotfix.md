# Page Momentum Legacy Test Output Hotfix

## Issue

Phase 1.57 polished the `/admin/page-momentum` panel but removed two strings that older readiness tests still assert:

- `Core decoupling readiness`
- `PageRenderer report-only candidate`

The controller was working, but the historical architecture test still used these strings as a continuity guard.

## Fix

`PageMomentumStatusProvider` now keeps both legacy phrases in the rendered read-only panel while preserving the newer live-readiness cards.

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumWiringReadinessTest.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumLivePanelTest.php
php8.5 tools/smoke-page-admin-momentum-live-panel.php
php8.5 vendor/bin/pest
```
