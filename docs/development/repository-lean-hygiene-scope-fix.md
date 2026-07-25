# Repository Lean Hygiene Scope Fix

## Issue

The first strict version of `audit-repository-lean-hygiene.php` failed on every test across the repo that referenced a tool path. This surfaced useful future technical debt, but it was too broad for the immediate Page Momentum cleanup phase.

## Fix

- `--strict` now focuses on Page Momentum/process artefact hygiene.
- Global tool-pinning tests are reported as observations by default.
- `--global-strict` intentionally fails on all tool-pinning tests when we choose to attack that wider cleanup later.

## Commands

```bash
php8.5 tools/audit-repository-lean-hygiene.php
php8.5 tools/audit-repository-lean-hygiene.php --strict
php8.5 tools/audit-repository-lean-hygiene.php --global-strict
```
