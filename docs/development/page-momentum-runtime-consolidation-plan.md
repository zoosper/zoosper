# Phase 1.65m-z: Page Momentum Runtime Consolidation Planner

## Purpose

This phase prepares the next cleanup pass for remaining Page Momentum runtime/config scaffolding.

It protects the expected live dashboard core and identifies which remaining bridge/hook/candidate/config files have no active runtime references.

## Command

Dry-run:

```bash
php8.5 tools/plan-page-momentum-runtime-consolidation.php
```

Apply safe quarantine only after reviewing the dry-run:

```bash
php8.5 tools/plan-page-momentum-runtime-consolidation.php --apply
```

## Safety

- Dry-run by default.
- Only candidates with no active runtime references are safe to quarantine.
- Expected live dashboard runtime files are protected.
- Apply mode moves files to `var/quarantine/page-momentum-runtime-candidates/<timestamp>/`.
- A restore script is generated.
