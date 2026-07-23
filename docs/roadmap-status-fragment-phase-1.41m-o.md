## Phase 1.41m-o: Method plugin diagnostics guards

Status: ready to apply

Adds descriptive method plugin exceptions, validation issue/result objects, config validator, diagnostics proof, audit tooling, tests, and documentation. No production runtime path is intercepted.

Verification gates:

- `php8.5 tools/prove-method-plugin-diagnostics.php`
- `php8.5 tools/audit-method-plugin-diagnostics.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginDiagnosticsTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
