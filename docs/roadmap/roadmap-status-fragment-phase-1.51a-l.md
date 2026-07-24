## Phase 1.51a-l: Page momentum admin aggregation bridge

Status: ready to apply

Adds route/menu bridge classes that expose the isolated Page Momentum candidate in a conventional aggregation shape. The bridge produces one route and one menu item without mutating existing admin aggregators.

Safety:

- Existing aggregator files are not overwritten.
- Live mutation remains false.
- Candidate config remains isolated and reversible.

Verification gates:

- `php8.5 tools/prove-page-admin-momentum-admin-aggregation-bridge.php`
- `php8.5 tools/audit-page-admin-momentum-admin-aggregation-bridge.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumAdminAggregationBridgeTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
