# Tools Inventory Report Generation

Phase 1.37w moved the legacy verification-tool migration process towards durable Pest coverage.

Phase 1.37w.3 restores a deterministic operator-facing report generator for the current tools inventory.

## Command

Run from the repository root:

```bash
php8.5 tools/generate-tools-inventory-report.php
```

## Generated files

The command writes:

```text
var/reports/tools-inventory.txt
var/reports/tools-inventory.log
```

Generated files are runtime artefacts and should not normally be committed.

## Classification categories

### MIGRATE_TO_PEST

Legacy `tools/verify-*.php` scripts are classified as migration candidates unless they are explicitly protected as operational tools.

### KEEP_OPS

Operational scripts remain in `tools/`. These include audit, diagnose, inspect, repair, smoke, clean, publish, sync, generate, normalise, ensure, bootstrap, demo, reset, send, start, stop, quarantine, remove, pilot, page-content, and migration-fix tools.

### DELETE_NOW

Reserved for files that are confirmed obsolete and safe to delete immediately. The generator does not classify files as `DELETE_NOW` automatically.

### REVIEW

Reserved for ambiguous files. The generator keeps the default conservative: unknown non-verify tools stay operational rather than being marked for deletion.

## Safety notes

The report generator reads filenames only. It does not read `.env` and does not inspect file contents.

## Commit hygiene

Commit the generator, tests, and documentation. Do not commit generated `var/reports` output unless a report is intentionally promoted to docs.
