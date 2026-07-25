# Phase 1.74m-z: Architecture Foundation Gate Aggregator

## Purpose

This phase adds one top-level audit for Zoosper's core architecture foundation gates.

It does not replace focused guards. Instead, it confirms they are present and checks for obvious drift from the foundation direction.

## What it checks

- Required architecture guard tools and tests exist.
- `app/zoosper-core/src` does not directly reference feature-module namespaces such as `Zoosper\Page\` or `Zoosper\Site\`.
- Temporary fixer/hotfix artefacts are reported as warnings so the repository stays lean.

## Why this matters

After a heavy foundation arc, the risk shifts from implementation bugs to process drift. This audit gives us one fast view of whether the guard rails still exist and whether temporary repair artefacts have leaked into the repo.

## Command

```bash
php8.5 tools/audit-architecture-foundation-gates.php
```

## Output

```text
var/reports/architecture-foundation-gates.txt
var/reports/architecture-foundation-gates.json
```
