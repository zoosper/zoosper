## Phase 1.75a-l: Architecture foundation verification runner

Status: ready to apply

Adds a permanent read-only runner for the architecture foundation gate set.

Safety:

- Read-only verification only.
- Runs architecture audit tools only.
- Does not modify runtime PHP files.
- Does not run Composer or Pest internally.

Verification gates:

- `php8.5 -l tools/verify-architecture-foundation.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/ArchitectureFoundationVerificationRunnerTest.php`
- `php8.5 tools/verify-architecture-foundation.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
