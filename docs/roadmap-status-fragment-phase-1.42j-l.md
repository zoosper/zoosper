## Phase 1.42j-l: Selected candidate risk readiness

Status: ready to apply

Adds selected-candidate risk notes, rollback checklist, readiness audit, tests, and documentation. Runtime interception remains disabled by default.

Verification gates:

- `php8.5 tools/write-method-plugin-selected-candidate-risk-notes.php`
- `php8.5 tools/write-method-plugin-selected-candidate-rollback-checklist.php`
- `php8.5 tools/audit-method-plugin-selected-candidate-risk-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateRiskReadinessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
