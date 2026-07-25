## Phase 1.65m-z: Page Momentum runtime consolidation planner

Status: ready to apply

Adds a dry-run-first planner for safely consolidating remaining non-core Page Momentum runtime/config scaffolding.

Safety:

- Dry-run by default.
- Expected live dashboard runtime core is protected.
- Apply mode quarantines only files with no active runtime references.

Verification gates:

- `php8.5 -l tools/plan-page-momentum-runtime-consolidation.php`
- `php8.5 tools/plan-page-momentum-runtime-consolidation.php`
- optional: `php8.5 tools/plan-page-momentum-runtime-consolidation.php --apply`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 tools/audit-repository-file-count-baseline.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
