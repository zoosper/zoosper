# MySQL `CREATE INDEX IF NOT EXISTS` Failure

## Symptom

`php bin/zoosper migrate` fails with syntax error near:

```sql
IF NOT EXISTS `idx_admin_user_two_factor_user_method`
```

## Cause

The previous declarative schema applier emitted `CREATE INDEX IF NOT EXISTS`. MySQL/MariaDB deployments do not consistently support that syntax.

## Fix

The replacement `DeclarativeSchemaApplier` now checks index existence first using metadata and then runs plain:

```sql
CREATE INDEX index_name ON table_name (...)
```

or:

```sql
CREATE UNIQUE INDEX index_name ON table_name (...)
```

This keeps the operation idempotent without relying on unsupported SQL syntax.
