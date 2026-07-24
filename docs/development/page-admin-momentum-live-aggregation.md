# Phase 1.56a-z: Page Momentum Live Aggregation

## Goal

Wire the Page Momentum admin route/menu hook into page-module route/menu config files so the real admin aggregation path can consume `/admin/page-momentum`.

## What the apply tool writes

The guarded apply tool writes or updates:

- `app/zoosper-page/config/admin_routes.php` or existing `routes.php` / `admin_routes.php`
- `app/zoosper-page/config/admin_menu.php`

It appends only:

- route: `admin.page_momentum.index`
- path: `/admin/page-momentum`
- permission: `page.manage`
- menu route: `admin.page_momentum.index`

## Safety model

- Existing files are backed up before writes.
- Duplicate route/menu entries are avoided.
- Core router internals are not edited.
- Rollback is available from `var/backups/page-admin-momentum-live-aggregation`.

## Verification

```bash
php8.5 tools/apply-page-admin-momentum-live-aggregation.php
php8.5 tools/audit-page-admin-momentum-live-aggregation.php
php8.5 tools/smoke-page-admin-momentum-live-files.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumPhase156LiveAggregationTest.php
php8.5 vendor/bin/pest
```
