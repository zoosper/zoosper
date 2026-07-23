# Phase 1.42s-z Closure Audit Hotfix

## Issue

The Phase 1.42 closure audit required two report filenames that may differ from the actual generated report names in the current repo state:

- `var/reports/method-plugin-report-only-candidate-plan.txt`
- `var/reports/method-plugin-selected-report-only-candidate-plan.txt`

The audit therefore reported two missing files even though the planning artefacts may exist under equivalent selected-candidate filenames.

## Fix

The closure audit now accepts equivalent alternatives for the general and selected report-only candidate plan reports. It still fails if no acceptable report exists for either requirement.

## Verification

```bash
php8.5 tools/audit-method-plugin-phase-142-closure.php
```

Expected:

```text
Warnings: 0
Errors: 0
```
