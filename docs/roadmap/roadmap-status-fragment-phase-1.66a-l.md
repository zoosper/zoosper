## Phase 1.66a-l: Page Momentum post-runtime support cleanup

Status: ready to apply

Adds a dry-run-first support cleanup tool for docs/tools that still reference Page Momentum runtime candidates quarantined during Phase 1.65m-z.

Safety:

- Dry-run by default.
- Runtime source under `app/` is never moved.
- Apply mode quarantines support files and writes a restore script.

Verification gates:

- `php8.5 -l tools/cleanup-page-momentum-post-runtime-support-artifacts.php`
- `php8.5 tools/cleanup-page-momentum-post-runtime-support-artifacts.php`
- optional: `php8.5 tools/cleanup-page-momentum-post-runtime-support-artifacts.php --apply`
- `php8.5 tools/audit-page-momentum-runtime-dependencies.php`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 tools/audit-repository-file-count-baseline.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
