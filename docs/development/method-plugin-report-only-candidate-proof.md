# Phase 1.42d-f: Method Plugin Report-Only Candidate Proof

## Goal

Select one safe candidate from the Phase 1.42a-c discovery report and write a disabled-by-default report-only opt-in plan for that exact invocation key.

## Safety model

- The selected candidate is only written to reports.
- Runtime execution remains disabled by default.
- No allow-list is applied to application runtime config.
- The next proof must use explicit fixture/sample input only.

## Verification

```bash
php8.5 tools/select-method-plugin-report-only-candidate.php
php8.5 tools/write-method-plugin-report-only-candidate-plan.php
php8.5 tools/audit-method-plugin-report-only-candidate-proof.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginReportOnlyCandidateProofTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.42g-i should build a report-only dry-run harness for the selected invocation key using sample/fixture input only.
