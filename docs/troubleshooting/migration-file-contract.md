# Migration File Contract Hotfix

## Symptom

`bin/zoosper migrate` reports:

```text
RuntimeException: Bad migration file: database/migrations/20260710002600_apply_module_declarative_schemas.php
```

## Cause

The current `Migrator` rejected the bridge file when it executed code directly and also when it returned a callable. This strongly indicates the current migrator expects migration files to return SQL statement arrays.

## Fix

The replacement migration returns an array of SQL statements. It creates the current missing Phase 0.24/0.25 tables:

```text
url_rewrites
admin_user_two_factor
admin_user_recovery_codes
admin_two_factor_challenges
```

## Future correction

The long-term fix should be to update `Migrator` so `bin/zoosper migrate` applies every enabled module's `config/db_schema.php` through `DeclarativeSchemaApplier`. This hotfix unblocks the current schema creation without changing current core migrator files.
