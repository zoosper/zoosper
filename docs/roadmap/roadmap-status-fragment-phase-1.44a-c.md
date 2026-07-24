## Phase 1.44a-c: Core decoupling readiness

Status: ready to apply

Starts the reviewer-recommended Decouple arc by auditing `zoosper-core` downstream feature-module dependencies and writing a concrete decoupling plan.

Scope:

- Audit core imports of downstream modules.
- Produce a report-only violation snapshot.
- Write the fallback/site-context/console decoupling plan.
- Add a readiness regression test and documentation.

Safety:

- Runtime is not changed.
- Fallback routing is not rewired yet.
- Site context binding is not changed yet.

Verification gates:

- `php8.5 tools/audit-core-downstream-module-dependencies.php`
- `php8.5 tools/plan-core-decoupling-phase-144.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/CoreDecouplingReadinessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
