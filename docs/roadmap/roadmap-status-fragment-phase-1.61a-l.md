## Phase 1.61a-l: Page Admin dashboard status system

Status: ready to apply

Adds visual status badge rendering to `/admin/page-momentum` so dashboard cards are easier to scan.

Safety:

- Read-only UI only.
- No database writes.
- No route/menu mutation.

Verification gates:

- `php8.5 tools/smoke-page-admin-dashboard-status-system.php`
- `php8.5 tools/audit-page-admin-dashboard-status-system.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminDashboardStatusSystemTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
