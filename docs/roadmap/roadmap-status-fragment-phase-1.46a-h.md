## Phase 1.46a-h: Page admin momentum wiring readiness

Status: ready to apply

Adds a page-module admin controller stub for the momentum panel, updates disabled route/menu metadata to reference that controller, adds proof/audit tooling, tests, and documentation.

Safety:

- Runtime route is not registered yet.
- Admin menu item remains disabled.
- Controller is read-only/static.
- Existing runtime routing is unchanged.

Verification gates:

- `php8.5 tools/prove-page-admin-momentum-controller.php`
- `php8.5 tools/audit-page-admin-momentum-wiring-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumWiringReadinessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
