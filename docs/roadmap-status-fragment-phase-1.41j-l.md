## Phase 1.41j-l: Method plugin resolver factory seam

Status: ready to apply

Adds a plugin resolver interface, default reflection resolver, resolver-backed factory, safe sample-service proof, audit tooling, tests, and documentation. No production runtime path is intercepted.

Verification gates:

- `php8.5 tools/prove-method-plugin-resolver-factory.php`
- `php8.5 tools/audit-method-plugin-resolver-factory.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginResolverFactoryTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
