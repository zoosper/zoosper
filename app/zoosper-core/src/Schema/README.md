# Zoosper Core Schema

The schema package provides the Phase 0.12 declarative schema hardening foundation.

## Main services

- `SchemaLoader` discovers module `config/db_schema.php` files.
- `SchemaValidator` validates schema declarations before SQL is generated.
- `SchemaMigrator` generates and applies safe additive SQL.
- `SchemaSnapshotRepository` records applied SQL batches.

## Current safety policy

Only additive operations are generated:

- create table
- add column
- add index

No destructive operation is generated in Phase 0.12.
