## Phase 1.47a-l: Page admin momentum runtime bridge

Status: ready to apply

Adds route/menu definition providers and a combined runtime bridge for the page admin momentum panel. The bridge proves disabled-by-default behaviour and fixture-enabled export without registering live routes or menu items.

Safety:

- Metadata remains disabled by default.
- Live route is not registered.
- Live menu item is not enabled.
- Bridge only normalises definitions for future wiring.

Verification gates:

- `php8.5 tools/prove-page-admin-momentum-runtime-bridge.php`
- `php8.5 tools/audit-page-admin-momentum-runtime-bridge.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumRuntimeBridgeTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
