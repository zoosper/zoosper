# Core schema engine

The Core schema engine discovers module `config/db_schema.php` declarations, validates them and generates safe additive SQL. Supported automatic operations are table creation, column addition and index addition. Destructive schema changes require explicit migrations.

See the canonical [architecture guide](../../../../../docs/architecture.md) and [module guide](../../../../../docs/modules.md).
