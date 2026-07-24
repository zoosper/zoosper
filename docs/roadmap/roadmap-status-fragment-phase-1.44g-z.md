## Phase 1.44g-z: Core decoupling readiness closure

Status: ready to apply

Closes the Phase 1.44 decoupling-readiness arc by adding safe feature-module adapter stubs, downstream coupling snapshot, closure audit, tests, and documentation.

Safety:

- Runtime fallback is not rewired.
- Runtime site context binding is not changed.
- Remaining core downstream references are expected until later cutover phases.

Verification gates:

- `php8.5 tools/audit-feature-module-decoupling-adapters.php`
- `php8.5 tools/audit-core-downstream-after-phase-144.php`
- `php8.5 tools/audit-core-decoupling-phase-144-closure.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/CoreDecouplingPhase144ClosureTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
