# Zoosper Database Package

Database connection and schema management for Zoosper CMS.

## Responsibilities

This package is responsible for:
- Managing the PDO connection lifecycle and providing the `PDO` instance to the container.
- Implementing the `Migrator` and `SchemaMigrator` for database schema evolution.
- Handling database configuration resolution (DSN, credentials, drivers).
- Providing the `MigrateCommand` and `MigrationStatusCommand` CLI tools.
- Ensuring atomic migrations and reliable schema discovery across modules.
- Abstracting the underlying database driver (supporting MySQL and SQLite).

## Architecture

The Database package is a low-level infrastructure module. It provides the foundation for all other modules that require persistence. It is designed to be highly resilient and to operate even when the main application boot is partially failing (e.g., during initial setup or migration).

## Migrations

Module-owned migrations are discovered automatically if they are placed in a `database/migrations` directory within a module. The `Migrator` service handles the sequencing and execution of these migrations, keeping track of the state in the `schema_migrations` table.

## Dependencies

This package depends on:
- `ext-pdo` (PHP PDO extension)

## Resilience

The `ConnectionFactory` and `PdoConnectionProvider` are designed to handle connection failures gracefully, providing useful error messages and avoiding unhandled exceptions during early boot or CLI discovery phases.

## Testing

The Database package includes unit tests for the `ConnectionFactory` and `Migrator`.

- Full repository suite: `zcomposer test`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

When using the `MigrateCommand`, ensure that the database user has sufficient privileges to modify the schema. Schema changes are performed within a transaction where the driver supports it, ensuring atomicity.

## Foreign-key reconciliation

Module-owned `config/db_schema.php` files declare named foreign keys with child columns, referenced tables and columns, and explicit actions where behaviour differs from the restrictive default.

Operational workflow:

1. Run `php8.5 bin/zoosper schema:foreign-keys:status --format=json` as the read-only inspection step.
2. Resolve orphan data, type incompatibilities, missing parent uniqueness, and missing child indexes before application.
3. Apply reviewed MySQL additions with `php8.5 bin/zoosper schema:foreign-keys:apply --confirm=apply`.
4. Re-run status and require zero additions, mismatches, and SQLite rebuild requirements.

Existing-table application is explicit, MySQL-only, confirmation-gated, and snapshot-recorded. MySQL DDL can partially succeed, so a failed apply must be inspected before retrying. SQLite existing-table constraints require explicit data-preserving rebuild migrations; they are never applied invisibly. Ordinary `migrate` does not perform existing-table FK reconciliation.

The current first-party inventory contains 33 declarative relationships. Fresh SQLite installation and the active MySQL schema both reconcile all 33, and `release:check` blocks release readiness when reconciliation is incomplete or inspection fails.
