## Phase 1.74m-z: Architecture foundation gate aggregator

Status: ready to apply

Adds a read-only top-level audit for permanent architecture guards and repository hygiene after the Page/Site decoupling and Site lookup service-binding arc.

Safety:

- Read-only audit only.
- No runtime PHP changes.
- Temporary artefacts produce warnings, not hard failures.

Verification gates:

- `php8.5 -l tools/audit-architecture-foundation-gates.php`
- `php8.5 tools/audit-architecture-foundation-gates.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-decoupling-closure.php`
- `php8.5 tools/audit-site-lookup-boundary-regression.php`
- `php8.5 tools/audit-site-lookup-service-binding.php`
- `php8.5 tools/audit-site-lookup-service-binding-regression.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
