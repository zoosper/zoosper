## Phase 1.40s-t: AdminFormConfigAggregator functional parity readiness

Status: ready to apply

Adds contract discovery and readiness audit tooling for the layered `AdminFormConfigAggregator` before generating a direct functional parity fixture against the aggregator's live public API.

Verification gates:

- `php8.5 tools/discover-admin-form-config-aggregator-contract.php`
- `php8.5 tools/audit-admin-form-config-aggregator-functional-parity-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigAggregatorFunctionalParityReadinessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
