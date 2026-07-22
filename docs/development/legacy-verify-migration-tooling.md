# Legacy Verify Migration Tooling

Phase 1.37w established the policy for migrating legacy `tools/verify-*` scripts into durable Pest tests.

This tooling bundle adds the inspection and planning commands needed to perform that migration without bulk deletion.

## Commands

### Inspect all legacy verify scripts

```bash
php8.5 tools/inspect-legacy-verify-migration.php
```

This writes:

```text
var/reports/legacy-verify-migration-inspection.txt
var/reports/legacy-verify-migration-inspection.log
```

### Plan one script migration

```bash
php8.5 tools/plan-legacy-verify-migration.php --script=tools/verify-project-structure.php
```

This writes a script-specific migration plan under `var/reports/`.

## Safety model

The tools are report/planning helpers only. They do not delete source files and do not rewrite existing tests.

A legacy verify script can be removed only after equivalent Pest coverage is added or confirmed.

## Commit hygiene

Commit the tools, tests, and documentation. Keep generated `var/reports` files uncommitted unless intentionally promoted to documentation.
