# Phase 1.65a-l: Page Momentum Runtime Dependency Audit

## Purpose

After the Page Momentum cleanup and repository baseline phases, we still need to understand which remaining Page Momentum files are truly runtime-critical and which can be folded or removed later.

This phase adds one read-only audit that classifies remaining active Page Momentum files into:

- keepRuntime
- reviewCandidate
- configCandidate
- supportOnly
- missingExpectedRuntimeCore

## Command

```bash
php8.5 tools/audit-page-momentum-runtime-dependencies.php
```

## Output

```text
var/reports/page-momentum-runtime-dependencies.txt
var/reports/page-momentum-runtime-dependencies.json
```

## Safety

The audit is read-only. It does not delete, move, or rewrite files.
