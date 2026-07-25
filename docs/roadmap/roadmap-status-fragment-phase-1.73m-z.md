## Phase 1.73m-z: Site lookup service binding finalisation

Status: ready to apply

Adds a dry-run-first guarded patcher and audit for binding `SiteLookupInterface` to the Site-module `DatabaseSiteLookup` adapter.

Safety:

- Dry-run by default.
- Backup before apply.
- No core runtime source coupling.
- Refuses unknown config shapes.

Verification gates:

- `php8.5 -l tools/apply-site-lookup-service-binding.php`
- `php8.5 -l tools/audit-site-lookup-service-binding.php`
- `php8.5 tools/apply-site-lookup-service-binding.php`
- optional: `php8.5 tools/apply-site-lookup-service-binding.php --apply`
- `php8.5 tools/audit-site-lookup-service-binding.php`
- `php8.5 tools/audit-site-lookup-wiring-readiness.php`
- `php8.5 tools/plan-site-lookup-wiring-finalisation.php`
- `php8.5 tools/audit-site-lookup-boundary-regression.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-decoupling-closure.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
