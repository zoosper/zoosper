## Phase 1.70m-z: Site lookup boundary foundation

Status: ready to apply

Adds the core-owned Site lookup contract, null implementation, immutable resolved-site DTO, and Site-module database adapter foundation.

Safety:

- No runtime cutover yet.
- No database changes.
- Core-owned files do not import the Site module.

Verification gates:

- `php8.5 -l app/zoosper-core/src/Site/ResolvedSite.php`
- `php8.5 -l app/zoosper-core/src/Site/SiteLookupInterface.php`
- `php8.5 -l app/zoosper-core/src/Site/NullSiteLookup.php`
- `php8.5 -l app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php`
- `php8.5 tools/audit-site-lookup-boundary-foundation.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryFoundationTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
