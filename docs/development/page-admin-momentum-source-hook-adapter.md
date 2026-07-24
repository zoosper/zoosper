# Phase 1.53a-l: Page Momentum Source Hook Adapter

## Goal

Add an isolated source-level adapter that exposes the Page Momentum admin hook candidate in a stable route/menu array shape for the real admin aggregation pipeline to consume later.

## Safety model

- Existing admin route/menu aggregators are not edited.
- Adapter config is isolated at `app/zoosper-page/config/admin_page_momentum_source_hook_adapter.php`.
- Live mutation remains false.
- The adapter exports one route and one menu item when the hook candidate is enabled.

## Verification

```bash
php8.5 tools/prove-page-admin-momentum-source-hook-adapter.php
php8.5 tools/audit-page-admin-momentum-source-hook-readiness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumSourceHookAdapterTest.php
php8.5 vendor/bin/pest
```
