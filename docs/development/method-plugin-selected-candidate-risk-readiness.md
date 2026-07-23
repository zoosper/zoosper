# Phase 1.42j-l: Selected Candidate Risk Readiness

## Goal

Document the selected method plugin report-only candidate's risk notes and rollback checklist before any actual invocation wrapper is attempted.

## Safety model

- Production runtime interception remains disabled.
- No invocation key is added to runtime config.
- The selected candidate is documented only.
- Rollback checklist must exist before any future real report-only proof.

## Verification

```bash
php8.5 tools/write-method-plugin-selected-candidate-risk-notes.php
php8.5 tools/write-method-plugin-selected-candidate-rollback-checklist.php
php8.5 tools/audit-method-plugin-selected-candidate-risk-readiness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateRiskReadinessTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.42m-o should add a fixture-input contract for the selected candidate, still without invoking production services.
