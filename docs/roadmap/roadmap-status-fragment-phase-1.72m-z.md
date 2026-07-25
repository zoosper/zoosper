## Phase 1.72m-z: Site lookup boundary regression guard

Status: ready to apply

Adds permanent Site lookup boundary regression coverage after the Page/Site core-feature decoupling arc.

Safety:

- Read-only audit/test coverage only.
- No runtime PHP source changes.
- No temporary fixer artefacts.

Verification gates:

- `php8.5 tools/audit-site-lookup-boundary-regression.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryRegressionTest.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-decoupling-closure.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
