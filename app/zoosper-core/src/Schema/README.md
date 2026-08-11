# Core schema engine

The Core schema engine discovers module `config/db_schema.php` declarations, validates them and generates safe additive SQL. Supported automatic operations are table creation, column addition and index addition. Destructive schema changes require explicit migrations.

See the canonical [architecture guide](../../../../../docs/architecture.md) and [module guide](../../../../../docs/modules.md).

## Foreign keys

Tables may declare `foreign_keys` beside `columns` and `indexes`. Each named definition provides `columns`, `referenced_table`, `referenced_columns`, and optional `on_delete` / `on_update` actions. `RESTRICT` is the default. `CASCADE`, `SET NULL`, and `NO ACTION` must be explicit; `SET NULL` requires nullable local columns. Phase 9HI emits constraints when creating fresh MySQL or SQLite tables. Existing-table introspection and reconciliation are deliberately deferred to Phase 9HJ so SQLite tables are never rebuilt implicitly.

## Existing-table foreign-key reconciliation

`SchemaForeignKeyInspector` reads live MySQL constraints from `INFORMATION_SCHEMA` and SQLite constraints from `PRAGMA foreign_key_list`. `SchemaForeignKeyReconciliationPlanner` classifies each declared constraint as present, addable on MySQL, mismatched, or requiring an explicit SQLite rebuild migration. Planning is read-only. It never executes DDL, drops a constraint, or rebuilds a SQLite table. MySQL additions are exposed as deterministic `ALTER TABLE ... ADD CONSTRAINT` statements for a later explicit apply boundary.

## Operator commands

`php bin/zoosper schema:foreign-keys:status` is the read-only reconciliation report. Add `--format=json` for machine-readable output. `php bin/zoosper schema:foreign-keys:apply --dry-run=1` delegates to the same report. Applying requires `--confirm=apply`, executes only missing MySQL constraints, blocks mismatches and SQLite rebuild requirements, and records successful statements in `schema_snapshots`. Ordinary `migrate` remains unchanged and never applies existing-table foreign keys implicitly.
