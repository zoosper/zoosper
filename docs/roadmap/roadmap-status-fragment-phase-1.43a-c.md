## Phase 1.43a-c: Config-layered method plugin runtime configuration discovery

Status: ready to apply

Adds method plugin runtime config array loader, layered config loader, runtime config layering proof, audit tooling, tests, and documentation.

Safety:

- Runtime remains disabled by default.
- Disabled runtime resolves to an empty allow-list.
- Config discovery does not invoke selected services.
- Production runtime interception remains disabled.

Verification gates:

- `php8.5 tools/prove-method-plugin-runtime-config-layering.php`
- `php8.5 tools/audit-method-plugin-runtime-config-layering.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginRuntimeConfigLayeringTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
