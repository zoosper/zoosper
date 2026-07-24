## Phase 1.56a-z: Page momentum live aggregation

Status: ready to apply

Adds a guarded live aggregation apply tool for wiring Page Momentum into page-module route/menu config, plus audit tools, smoke tooling, tests, documentation, and rollback notes.

Safety:

- Core router internals are not edited.
- Config files are backed up before writes.
- Duplicate entries are avoided.
- Rollback is documented.

Verification gates:

- `php8.5 tools/apply-page-admin-momentum-live-aggregation.php`
- `php8.5 tools/audit-page-admin-momentum-live-aggregation.php`
- `php8.5 tools/smoke-page-admin-momentum-live-files.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumPhase156LiveAggregationTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
