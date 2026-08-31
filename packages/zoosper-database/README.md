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
