# Runtime-Guarded Page Momentum Process-Debt Cleanup

The first guarded cleanup tool was too cautious because it treated old docs/tools/tests references as blockers.

This version blocks only production/runtime references in `app/` excluding tests. That means old phase docs/tools/tests can be quarantined together, while anything still referenced by real runtime config/source remains protected.

## Commands

```bash
php8.5 tools/cleanup-page-momentum-process-artifacts.php
php8.5 tools/cleanup-page-momentum-process-artifacts.php --apply
```
