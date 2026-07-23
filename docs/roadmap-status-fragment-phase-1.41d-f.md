## Phase 1.41d-f: Method plugin discovery and safe proof

Status: ready to apply

Adds a plugin factory, executor, file config loader, safe sample-service proof, audit tooling, tests, and documentation. No production runtime path is intercepted.

Verification gates:

- `php8.5 tools/prove-method-plugin-sample-service.php`
- `php8.5 tools/audit-method-plugin-discovery.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginDiscoveryAndSampleServiceTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
