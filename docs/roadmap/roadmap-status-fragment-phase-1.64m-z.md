## Phase 1.64m-z: Repository lean hygiene closure

Status: ready to apply

Adds a repository file-count baseline audit to help prevent future bloat after the Page Momentum cleanup arc.

Safety:

- Read-only by default.
- No file movement/deletion.
- Optional baseline JSON under `docs/metrics/`.

Verification gates:

- `php8.5 -l tools/audit-repository-file-count-baseline.php`
- `php8.5 tools/audit-repository-file-count-baseline.php`
- `php8.5 tools/audit-repository-file-count-baseline.php --write-baseline`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
