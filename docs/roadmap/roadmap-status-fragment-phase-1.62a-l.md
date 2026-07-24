## Phase 1.62a-l: Page Admin dashboard facts

Status: ready to apply

Adds real read-only facts to `/admin/page-momentum` for live route state, live menu state, renderer controller availability, and HTTP controller route binding.

Safety:

- Read-only inspection only.
- No database writes.
- No route/menu mutation.

Verification gates:

- `php8.5 tools/smoke-page-admin-dashboard-facts.php`
- `php8.5 tools/audit-page-admin-dashboard-facts.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminDashboardFactsTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
