## Phase 1.42m-r: Selected candidate fixture closure readiness

Status: ready to apply

Adds selected-candidate fixture contract, validation, no-invocation preflight, closure-readiness audit, tests, and documentation. Runtime interception remains disabled by default.

Verification gates:

- `php8.5 tools/write-method-plugin-selected-candidate-fixture-contract.php`
- `php8.5 tools/validate-method-plugin-selected-candidate-fixture-contract.php`
- `php8.5 tools/write-method-plugin-selected-candidate-no-invocation-preflight.php`
- `php8.5 tools/audit-method-plugin-selected-candidate-closure-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateClosureReadinessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
