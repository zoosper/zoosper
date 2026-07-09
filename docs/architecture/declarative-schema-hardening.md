# Declarative Schema Hardening

Phase 0.12 hardens the Phase 0.11 declarative schema MVP.

## Added in this phase

- schema validation
- schema snapshot recording
- `php bin/zoosper-schema validate`
- `php bin/zoosper-schema snapshots`
- version service for consistent CMS version display

## Validation checks

The validator checks for:

- tables without columns
- unsupported column types
- invalid column definitions
- nullable primary columns
- indexes without columns
- indexes referencing missing columns

## Snapshot behaviour

When `php bin/zoosper-schema apply` executes SQL statements, the SQL batch is saved to `schema_snapshots` with a SHA-256 hash and timestamp.

## Still intentionally deferred

- drop column/table support
- alter column type support
- foreign key reconciliation
- rename mapping
- allow-listed destructive schema changes
