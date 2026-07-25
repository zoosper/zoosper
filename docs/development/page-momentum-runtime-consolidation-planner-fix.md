# Page Momentum Runtime Consolidation Planner Dependency Fix

## Issue

The first planner checked each candidate independently. A file could appear safe because it had no direct non-candidate runtime references, while still being referenced by another candidate that was blocked and would remain active.

## Fix

The planner now propagates block status through candidate-to-candidate dependencies. If a blocked candidate remains active and references another candidate, that dependency is also blocked.

## Commands

```bash
php8.5 tools/plan-page-momentum-runtime-consolidation.php
php8.5 tools/plan-page-momentum-runtime-consolidation.php --apply
```
