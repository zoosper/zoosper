# Legacy Verify Controlled Removal

Legacy `tools/verify-*` scripts are being migrated into durable Pest coverage.

This document defines the controlled removal process. Deletion is intentionally gated because many legacy verify scripts contain historical source-contract intent that must not be lost.

## Current phase rule

In Phase 1.37w.10-w.12, the helper is primarily a dry-run and safety workflow. Do not remove pilot scripts yet while earlier migration-readiness tests still assert that the pilot batch remains source-owned.

## Removal rule

A legacy verify script may be deleted only when all of the following are true:

1. The script is in the approved pilot batch or has been explicitly added to a future migration batch.
2. The source contract asserted by the script has equivalent Pest coverage.
3. Tests that previously expected the script to exist have been updated to record it as migrated.
4. The full Pest suite is green before and after deletion.
5. The deletion is a focused commit.
6. Inventory and inspection reports are regenerated locally after deletion.
7. Generated `var/reports` files remain uncommitted unless intentionally promoted.

## Current approved pilot batch

The controlled removal helper currently recognises these scripts:

- `tools/verify-project-structure.php`
- `tools/verify-runtime-path-safety.php`
- `tools/verify-service-provider-manifest-file.php`
- `tools/verify-module-composer-manifests.php`
- `tools/verify-roadmap-planning-docs.php`

## Dry-run first

Always dry-run before deletion:

```bash
php8.5 tools/remove-migrated-legacy-verify.php --script=tools/verify-project-structure.php
```

## Apply deletion is deliberately hard-gated

`--apply` alone is not enough. A future deletion attempt must explicitly include both confirmations:

```bash
php8.5 tools/remove-migrated-legacy-verify.php   --script=tools/verify-project-structure.php   --apply   --confirm-pest-coverage   --confirm-remove
```

Do not run the apply command until the migration tests have been changed to treat that script as migrated.

## Safety model

The helper refuses to delete:

- paths outside `tools/`;
- scripts that do not start with `verify-`;
- non-PHP files;
- operational tools such as `audit-*`, `diagnose-*`, `inspect-*`, `repair-*`, `smoke-*`, or `sync-*`;
- legacy verify scripts that are not currently allowlisted;
- any script when `--apply` is used without both explicit confirmations.

## Commit hygiene

A migration commit should include only:

- the replacement or confirming Pest coverage;
- the deleted legacy verify script;
- any durable docs updates.

It should not include generated reports unless a report is intentionally promoted to documentation.
