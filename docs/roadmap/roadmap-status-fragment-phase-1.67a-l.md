## Phase 1.67a-l: Core feature coupling audit

Status: ready to apply

Adds a read-only audit for direct references from `zoosper-core/src` into feature/module namespaces, preparing safer runtime decoupling work.

Safety:

- Read-only audit only.
- No runtime code changes.
- Strict mode fails if forbidden namespace references are found.

Verification gates:

- `php8.5 -l tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-coupling.php --strict`
- `php8.5 tools/audit-page-momentum-cleanup-closure.php`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
