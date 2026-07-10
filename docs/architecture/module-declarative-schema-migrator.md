# Module Declarative Schema Migrator

This fix adds `Zoosper\Core\Database\DeclarativeSchemaApplier`.

## Purpose

The diagnostic output confirmed that module schema files are discovered, but the expected tables are missing. That means the issue is in the migrator path, not module discovery.

## Behaviour

The applier scans every enabled module for:

```text
config/db_schema.php
```

and creates missing tables/indexes idempotently.

## Safety

The applier only creates missing tables/indexes. It does not drop or alter existing columns, because destructive changes need a more carefully audited migration strategy.

## Immediate command

```bash
php tools/apply-module-db-schema.php
```

## bin/zoosper migrate bridge

A bridge migration is included:

```text
database/migrations/20260710002600_apply_module_declarative_schemas.php
```

If the existing migration runner executes PHP files in `database/migrations`, this will make `bin/zoosper migrate` apply all module schema files automatically.
