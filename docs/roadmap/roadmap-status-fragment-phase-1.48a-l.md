## Phase 1.48a-l: Page admin momentum live cutover preflight

Status: ready to apply

Adds a live cutover preflight service, deterministic route/menu cutover preview, rollback notes, readiness audits, tests, and documentation.

Safety:

- Live route is not registered.
- Live menu item is not enabled.
- Route/menu metadata remains disabled by default.
- Preview generation performs no live mutation.

Verification gates:

- `php8.5 tools/audit-page-admin-momentum-live-cutover-preflight.php`
- `php8.5 tools/generate-page-admin-momentum-cutover-preview.php`
- `php8.5 tools/audit-page-admin-momentum-phase-148-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumLiveCutoverPreflightTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
