## Phase 1.42a-c: Method plugin service candidate discovery

Status: ready to apply

Adds report-only method plugin candidate discovery, candidate planning reports, audit tooling, tests, and documentation. Runtime interception remains disabled by default.

Verification gates:

- `php8.5 tools/discover-method-plugin-service-candidates.php`
- `php8.5 tools/plan-method-plugin-report-only-candidates.php`
- `php8.5 tools/audit-method-plugin-service-candidate-discovery.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginServiceCandidateDiscoveryTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
