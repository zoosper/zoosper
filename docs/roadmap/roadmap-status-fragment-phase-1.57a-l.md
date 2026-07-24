## Phase 1.57a-l: Page momentum live panel polish

Status: ready to apply

Adds a read-only status provider and polishes the `/admin/page-momentum` controller output with route, permission, controller, and rollback status cards.

Safety:

- Panel remains read-only.
- No database writes.
- No route/menu aggregator internals are changed.

Verification gates:

- `php8.5 tools/smoke-page-admin-momentum-live-panel.php`
- `php8.5 tools/audit-page-admin-momentum-phase-157-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumLivePanelTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
