## Phase 1.44d-f: Core decoupling contracts

Status: ready to apply

Adds the first core-owned contracts for fallback routing and site context resolution, plus safe null defaults, audit tooling, tests, and documentation.

Safety:

- Runtime fallback is not rewired yet.
- Runtime site context binding is not changed yet.
- Existing concrete wiring remains in place until adapter/wiring tests are available.

Verification gates:

- `php8.5 tools/audit-core-decoupling-contracts.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/CoreDecouplingContractsTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
