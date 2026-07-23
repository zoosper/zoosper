## Phase 1.41s-u: Disabled-by-default method plugin runtime seam

Status: ready to apply

Adds runtime config and integration seam for report-only method plugin execution. Defaults are disabled and execution requires explicit invocation allow-listing. Production service paths remain untouched.

Verification gates:

- `php8.5 tools/prove-method-plugin-runtime-seam.php`
- `php8.5 tools/audit-method-plugin-runtime-seam.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginRuntimeSeamTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
