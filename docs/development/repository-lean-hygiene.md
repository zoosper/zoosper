# Phase 1.64a-l: Repository Lean Hygiene Guard

## Purpose

This phase adds a read-only audit to help keep Zoosper lean after the Page Momentum cleanup arc.

It helps detect:

- active old phase/process Page Momentum files outside quarantine;
- tests that still pin tool file paths instead of behaviour;
- generated `var/` artefacts that should remain untracked;
- Page Momentum docs/tools count after cleanup.

## Commands

Report mode:

```bash
php8.5 tools/audit-repository-lean-hygiene.php
```

Strict mode:

```bash
php8.5 tools/audit-repository-lean-hygiene.php --strict
```

## Safety

The audit is read-only. It does not delete, move, rewrite, or mutate files.

Reports are written to:

```text
var/reports/repository-lean-hygiene.txt
var/reports/repository-lean-hygiene.json
```
