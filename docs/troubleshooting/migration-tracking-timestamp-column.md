# Migration Tracking Timestamp Column Hotfix

## Symptom

`php bin/zoosper migrate` fails with:

```text
Field 'migrated_at' doesn't have a default value
```

## Cause

The existing `migrations` table uses `migrated_at`, not `applied_at`. The previous clean migrator attempted an `applied_at` insert, then fell back to inserting only the migration name. MySQL rejected that because `migrated_at` is NOT NULL and has no default.

## Fix

The replacement migrator detects the timestamp column from this ordered list:

```text
migrated_at
applied_at
executed_at
created_at
updated_at
```

and inserts `CURRENT_TIMESTAMP` into whichever one exists.

## Clean preference

This keeps the clean modern migrator approach while respecting the current migration tracking table shape.
