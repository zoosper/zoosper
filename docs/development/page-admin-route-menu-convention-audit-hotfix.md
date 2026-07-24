# Phase 1.45 Route/Menu Convention Audit Hotfix

## Issue

The page momentum route/menu convention audit reported one warning because `app/zoosper-page/config/routes.php` was not present.

That file should not be treated as a mandatory convention file by this audit because the current page module may use alternative route/controller convention files such as `config/controllers.php` or module route aggregation.

## Fix

The audit now separates:

- required page momentum artefacts, and
- optional convention discovery files.

Missing `routes.php` alone is no longer a warning if another route/controller convention file exists.

## Verification

```bash
php8.5 tools/audit-page-admin-route-menu-conventions.php
```

Expected result when `controllers.php` or another route convention exists:

```text
Errors: 0
```
