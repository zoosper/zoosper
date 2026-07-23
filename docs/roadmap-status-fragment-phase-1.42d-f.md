## Phase 1.42d-f: Method plugin report-only candidate proof

Status: ready to apply

Selects a safe report-only candidate from discovery reports and writes a disabled-by-default opt-in plan. Runtime interception remains disabled.

Verification gates:

- `php8.5 tools/select-method-plugin-report-only-candidate.php`
- `php8.5 tools/write-method-plugin-report-only-candidate-plan.php`
- `php8.5 tools/audit-method-plugin-report-only-candidate-proof.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginReportOnlyCandidateProofTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
