## Phase 1.43g-z: Method plugin runtime config planning closure

Status: ready to apply

Closes Phase 1.43 by adding bootstrap/config drift audits, method plugin runtime config shape checks, selected-candidate fixture readiness closure, final closure audit, tests, and documentation.

Safety:

- Runtime remains disabled by default.
- Selected service is not invoked.
- Production runtime interception remains disabled.
- Refined fixture contract remains fixture-only.

Verification gates:

- `php8.5 tools/audit-bootstrap-config-drift.php`
- `php8.5 tools/audit-method-plugin-phase-143-closure.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginPhase143ClosureTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
