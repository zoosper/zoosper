# Phase 1.37h.3 — Package module autoload hotfix

## Problem

After removing `app/zoosper-media`, `packages/*/tests` were correctly included in the root test suite, but root Composer autoload no longer mapped `Zoosper\\Media\\` because `ModuleAutoloadSynchronizer` did not scan `packages/*/module.php`.

## Fix

Update `ModuleAutoloadSynchronizer` to include:

```text
packages/*/module.php
```

when discovering module source and test PSR-4 mappings.
