## Phase 1.41g-i: Module-owned method plugin discovery

Status: ready to apply

Adds module `config/plugins.php` source discovery, module config loading, a sample-service module discovery proof, audit tooling, tests, and documentation. No production runtime path is intercepted.

Verification gates:

- `php8.5 tools/prove-method-plugin-module-discovery.php`
- `php8.5 tools/audit-method-plugin-module-discovery.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginModuleDiscoveryTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
