# Phase 1.58a-l: Page Admin Launch Readiness Dashboard

## Goal

Expand the live `/admin/page-momentum` panel into a broader read-only Page Admin launch-readiness dashboard.

## Added behaviour

- Adds `PageAdminLaunchReadinessProvider`.
- Keeps `PageMomentumStatusProvider` for live route/menu status.
- Updates `PageMomentumAdminController` to render two sections:
  - Live status
  - Page Admin launch-readiness dashboard
- Preserves continuity phrases:
  - `Core decoupling readiness`
  - `PageRenderer report-only candidate`

## Safety model

- Read-only dashboard.
- No forms.
- No database writes.
- No route/menu mutation.

## Verification

```bash
php8.5 tools/smoke-page-admin-launch-readiness-dashboard.php
php8.5 tools/audit-page-admin-launch-readiness-dashboard.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminLaunchReadinessDashboardTest.php
php8.5 vendor/bin/pest
```
