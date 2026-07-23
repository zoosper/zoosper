# Phase 1.42g-i: Selected Candidate Dry-Run Harness

## Goal

Create a dry-run harness plan for the selected report-only method plugin candidate without invoking production services or enabling runtime interception.

## Safety model

- The harness reads the selected candidate report.
- It writes a dry-run JSON plan only.
- It does not add the invocation key to runtime config.
- It does not invoke the selected service method.
- It requires explicit fixture/sample input in a future phase.

## Verification

```bash
php8.5 tools/write-method-plugin-selected-candidate-dry-run-harness.php
php8.5 tools/audit-method-plugin-selected-candidate-dry-run-harness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateDryRunHarnessTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.42j-l should add candidate-specific risk notes and rollback checklist before any actual report-only invocation wrapper is attempted.
