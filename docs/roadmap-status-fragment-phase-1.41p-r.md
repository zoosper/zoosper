## Phase 1.41p-r: Report-only method plugin executor

Status: ready to apply

Adds report-only plugin execution result/sink contracts, in-memory sink, report-only executor wrapper, safe sample proof, audit tooling, tests, and documentation. The wrapper requires explicit invocation allow-listing and returns baseline output.

Verification gates:

- `php8.5 tools/prove-method-plugin-report-only-executor.php`
- `php8.5 tools/audit-method-plugin-report-only-executor.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginReportOnlyExecutorTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
