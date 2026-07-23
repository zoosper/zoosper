## Phase 1.42s-z: Method plugin opt-in candidate planning closure

Status: ready to apply

Closes Phase 1.42 by adding final closure audit, runtime-disabled guard, candidate artefact completeness checks, documentation, and regression tests. Runtime interception remains disabled by default.

Verification gates:

- `php8.5 tools/audit-method-plugin-phase-142-closure.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginPhase142ClosureTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
