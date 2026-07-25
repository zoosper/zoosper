# Phase 1.64m-z: Repository File Count Baseline

## Purpose

This phase closes the repository lean-hygiene arc by adding a read-only file-count baseline audit.

After the Page Momentum cleanup removed/quarantined large amounts of process scaffolding, this audit gives us a simple way to measure whether future phases are growing the repository too quickly.

## Tool

```bash
php8.5 tools/audit-repository-file-count-baseline.php
```

Optional baseline write:

```bash
php8.5 tools/audit-repository-file-count-baseline.php --write-baseline
```

## What is counted

- app PHP files
- app test PHP files
- tool PHP files
- docs Markdown/text files
- config PHP files
- templates
- active Page Momentum files

## What is excluded

- `.git/`
- `vendor/`
- `var/`
- `node_modules/`
- IDE folders
- cache/temp folders

## Output

Reports:

```text
var/reports/repository-file-count-baseline.txt
var/reports/repository-file-count-baseline.json
```

Optional committed baseline:

```text
docs/metrics/repository-file-count-baseline.json
```
