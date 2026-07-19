# Phase 1.37m.3 - Schema CURRENT_TIMESTAMP default hotfix

## Goal

Fix the migration failure that prevented the media module's `media_assets` table from being created in the live MySQL/MariaDB database.

## Diagnosis

The SQL builder quoted the special `CURRENT_TIMESTAMP` default as a string literal. The runtime database rejected the generated `created_at` definition with `Invalid default value for 'created_at'`.

## Implemented

- Updated `SchemaSqlBuilder::defaultSql()` to emit `CURRENT_TIMESTAMP` as an unquoted SQL expression.
- Added regression coverage for MySQL and SQLite create-table SQL generation.
- Documented the migration failure and recovery commands.

## Follow-up

After applying this phase, run the real migration and then the media runtime schema diagnostic before retrying browser upload.
