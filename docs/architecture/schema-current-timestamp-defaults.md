# Declarative schema CURRENT_TIMESTAMP defaults

Phase 1.37m.3 fixes declarative schema SQL generation for special timestamp defaults.

## Problem

The media module declares:

```php
'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP']
```

The SQL builder previously treated every string default as a string literal, producing:

```sql
DEFAULT 'CURRENT_TIMESTAMP'
```

On the local MySQL/MariaDB runtime this caused migration to fail with:

```text
SQLSTATE[42000]: Syntax error or access violation: 1067 Invalid default value for 'created_at'
```

## Fix

`SchemaSqlBuilder::defaultSql()` now treats `CURRENT_TIMESTAMP` as a SQL expression and emits it unquoted:

```sql
DEFAULT CURRENT_TIMESTAMP
```

Other string defaults remain quoted and escaped.
