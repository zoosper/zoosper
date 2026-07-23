## Phase 1.41a-c: Method plugin/interceptor foundation

Status: ready to apply

Adds core method plugin primitives, an around-style chain runner, an ordered plugin registry, config loader, audit tooling, tests, and development documentation. Runtime paths are not intercepted yet.

Verification gates:

- `php8.5 tools/audit-method-plugin-foundation.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginFoundationTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
