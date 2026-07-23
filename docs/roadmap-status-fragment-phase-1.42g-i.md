## Phase 1.42g-i: Selected candidate dry-run harness

Status: ready to apply

Adds a dry-run harness plan for the selected report-only method plugin candidate. Runtime interception remains disabled and no production service is invoked.

Verification gates:

- `php8.5 tools/write-method-plugin-selected-candidate-dry-run-harness.php`
- `php8.5 tools/audit-method-plugin-selected-candidate-dry-run-harness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateDryRunHarnessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
