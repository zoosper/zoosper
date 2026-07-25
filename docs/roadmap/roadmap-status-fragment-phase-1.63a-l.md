## Phase 1.63a-l: Page Momentum process-debt cleanup

Status: ready to apply

Adds one reversible cleanup tool to inventory and quarantine Page Momentum process artefacts while keeping the working dashboard intact.

Safety:

- Dry-run by default.
- Apply mode quarantines rather than deletes.
- Restore script is generated for every apply batch.

Verification gates:

- `php8.5 -l tools/cleanup-page-momentum-process-artifacts.php`
- `php8.5 tools/cleanup-page-momentum-process-artifacts.php`
- optional: `php8.5 tools/cleanup-page-momentum-process-artifacts.php --apply`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
