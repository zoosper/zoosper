## Phase 1.64a-l: Repository lean hygiene guard

Status: ready to apply

Adds a read-only repository hygiene audit to prevent Page Momentum/process artefact bloat from returning.

Safety:

- Read-only audit only.
- Default mode exits 0 with warnings.
- Strict mode fails on active process artefacts or tests that pin tool paths.

Verification gates:

- `php8.5 -l tools/audit-repository-lean-hygiene.php`
- `php8.5 tools/audit-repository-lean-hygiene.php`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
