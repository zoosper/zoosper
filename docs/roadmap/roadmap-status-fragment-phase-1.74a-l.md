## Phase 1.74a-l: Site lookup service binding regression guard

Status: ready to apply

Adds durable audit/test coverage for the Site lookup service binding.

Safety:

- Read-only audit/test coverage only.
- No runtime PHP changes.
- No temporary fixer artefacts.

Verification gates:

- `php8.5 -l tools/audit-site-lookup-service-binding-regression.php`
- `php8.5 tools/audit-site-lookup-service-binding-regression.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/SiteLookupServiceBindingRegressionTest.php`
- `php8.5 tools/audit-site-lookup-service-binding.php`
- `php8.5 tools/audit-site-lookup-wiring-readiness.php`
- `php8.5 tools/audit-site-lookup-boundary-regression.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-decoupling-closure.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
