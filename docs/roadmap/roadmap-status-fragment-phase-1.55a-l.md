## Phase 1.55a-l: Page momentum route/menu hook

Status: ready to apply

Adds a passive route/menu hook that exports the Page Momentum admin route and menu item for a future live admin aggregation consumer patch.

Safety:

- Existing aggregator files are not overwritten.
- Hook is passive and read-only.
- Live mutation remains false.

Verification gates:

- `php8.5 tools/prove-page-admin-momentum-route-menu-hook.php`
- `php8.5 tools/audit-page-admin-momentum-route-menu-hook-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumRouteMenuHookTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
