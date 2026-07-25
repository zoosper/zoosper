## Phase 1.63m-z: Page Momentum support artefact cleanup

Status: ready to apply

Adds a dry-run-first support artefact cleanup tool to quarantine old Page Momentum docs/tools while preserving current dashboard/facts/status tooling.

Safety:

- Runtime source is not moved.
- Dry-run by default.
- Apply mode quarantines and writes a restore script.

Verification gates:

- `php8.5 -l tools/cleanup-page-momentum-support-artifacts.php`
- `php8.5 tools/cleanup-page-momentum-support-artifacts.php`
- optional: `php8.5 tools/cleanup-page-momentum-support-artifacts.php --apply`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
