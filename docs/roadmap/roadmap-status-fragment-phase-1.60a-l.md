## Phase 1.60a-l: Page Admin dashboard indicator rendering

Status: ready to apply

Renders the richer Page Admin dashboard indicators directly on `/admin/page-momentum` while keeping the dashboard read-only.

Safety:

- Read-only UI only.
- No database writes.
- No route/menu mutation.

Verification gates:

- `php8.5 tools/smoke-page-admin-dashboard-indicator-rendering.php`
- `php8.5 tools/audit-page-admin-dashboard-indicator-rendering.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminDashboardIndicatorRenderingTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
