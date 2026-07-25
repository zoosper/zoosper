## Phase 1.65a-l: Page Momentum runtime dependency audit

Status: ready to apply

Adds a read-only classifier for remaining active Page Momentum files after cleanup, preparing a safer consolidation pass.

Safety:

- Read-only audit only.
- No file movement/deletion.
- Reports runtime keep files, config candidates, review candidates, and support-only files.

Verification gates:

- `php8.5 -l tools/audit-page-momentum-runtime-dependencies.php`
- `php8.5 tools/audit-page-momentum-runtime-dependencies.php`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 tools/audit-repository-file-count-baseline.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
