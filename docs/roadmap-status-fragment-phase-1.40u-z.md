## Phase 1.40u-z: Config layering closure

Status: ready to apply

Closes Phase 1.40 by adding a final audit, backup hygiene warning, drift guard, closure test, and documentation for the config layering foundation.

Verification gates:

- `php8.5 tools/audit-config-layering-phase-140-closure.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/ConfigLayeringPhase140ClosureTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
