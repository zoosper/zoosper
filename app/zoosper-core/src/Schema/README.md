# Core schema engine

The Core schema engine discovers module `config/db_schema.php` declarations, validates them and generates safe additive SQL. Supported automatic operations are table creation, column addition and index addition. Destructive schema changes require explicit migrations.

See the canonical [architecture guide](../../../../../docs/architecture.md) and [module guide](../../../../../docs/modules.md).

## Foreign keys

Tables may declare `foreign_keys` beside `columns` and `indexes`. Each named definition provides `columns`, `referenced_table`, `referenced_columns`, and optional `on_delete` / `on_update` actions. `RESTRICT` is the default. `CASCADE`, `SET NULL`, and `NO ACTION` must be explicit; `SET NULL` requires nullable local columns. Phase 9HI emits constraints when creating fresh MySQL or SQLite tables. Existing-table introspection and reconciliation are deliberately deferred to Phase 9HJ so SQLite tables are never rebuilt implicitly.
