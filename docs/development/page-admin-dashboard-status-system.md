# Phase 1.61a-l: Page Admin Dashboard Status System

## Goal

Make the live `/admin/page-momentum` dashboard easier to read by rendering dashboard statuses as visual badges.

## Status classes

- `ready` -> green ready badge
- `active` -> green active badge
- `track` -> blue tracking badge
- `planned` -> purple planned badge
- `documented` -> grey documented badge
- `in-progress` -> amber in-progress badge

## Safety model

- Read-only UI only.
- No database writes.
- No route/menu mutation.
- No admin action forms.

## Verification

```bash
php8.5 tools/smoke-page-admin-dashboard-status-system.php
php8.5 tools/audit-page-admin-dashboard-status-system.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminDashboardStatusSystemTest.php
php8.5 vendor/bin/pest
```
