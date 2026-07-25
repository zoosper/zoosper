# Page Momentum Cleanup Closure and Hygiene Scope Fix

## Issue

After the Page Momentum cleanup closure phase, two read-only guards were too literal:

1. The cleanup closure audit counted cleanup tools/docs that define removed symbols as stale references.
2. The repository lean hygiene strict mode treated current/durable Page Momentum test-tool references as hard failures.

## Fix

- `audit-page-momentum-cleanup-closure.php` now excludes approved cleanup vocabulary files from stale-reference detection.
- `audit-repository-lean-hygiene.php --strict` now fails only on obsolete Page Momentum process-tool pins, while global/current tool pins remain observations unless `--global-strict` is used.

## Verification

```bash
php8.5 tools/audit-page-momentum-cleanup-closure.php
php8.5 tools/audit-repository-lean-hygiene.php --strict
```
