# Ledger-aware Legacy Verify Removal

The legacy verify removal workflow is now gated by the durable migration status ledger.

## Why this exists

A previous removal attempt deleted `tools/verify-project-structure.php` while other tests still treated it as source-owned. The ledger-aware gate prevents that class of mistake.

## Safe order

1. Inspect the legacy verify script.
2. Add or confirm equivalent Pest coverage.
3. Change the script status in `docs/development/legacy-verify-migration-status.md` from `source-owned` to `migrated`.
4. Run the full Pest suite.
5. Run the controlled removal helper with explicit confirmation flags.
6. Regenerate local reports.
7. Keep generated `var/reports` artefacts uncommitted unless intentionally promoted.

## Dry-run command

```bash
php8.5 tools/remove-migrated-legacy-verify.php --script=tools/verify-project-structure.php
```

## Apply command

The apply command is only allowed when the status ledger says `migrated`:

```bash
php8.5 tools/remove-migrated-legacy-verify.php   --script=tools/verify-project-structure.php   --apply   --confirm-pest-coverage   --confirm-remove
```

## Safety gates

The helper refuses removal when:

- the target is not an allowlisted pilot script;
- the path is unsafe;
- the script is not a `tools/verify-*.php` file;
- the apply confirmations are incomplete;
- the migration status ledger is missing;
- the ledger does not include the script;
- the ledger status is not `migrated`.

This means all current pilot scripts remain protected while they are marked `source-owned`.
