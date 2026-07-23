## Phase 1.40q-r: AdminFormConfigAggregator layered wiring

Status: ready to apply

Adds a guarded patcher, audit report, and Pest regression guard for wiring `AdminFormConfigAggregator` to the admin config layered runtime bridge.

Verification gates:

- `php8.5 tools/apply-admin-form-config-aggregator-layered-loader.php --dry-run`
- `php8.5 tools/apply-admin-form-config-aggregator-layered-loader.php --apply`
- `php8.5 tools/audit-admin-form-config-aggregator-layered-wiring.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigAggregatorLayeredWiringTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
