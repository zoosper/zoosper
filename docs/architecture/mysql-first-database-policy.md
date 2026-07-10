# Phase 0.37 - MySQL-first database policy

## Decision

Zoosper should be MySQL/MariaDB-first and production deployments should use a MySQL-family database.

SQLite can remain useful for very small local smoke tests, but it should not become a supported production target. Supporting SQLite as a first-class target would force extra compatibility work across migrations, indexes, SQL syntax, locking behaviour, schema introspection and runtime diagnostics.

## Policy

```text
Production: MySQL/MariaDB
Local smoke tests: SQLite allowed if explicitly enabled
Feature/module testing: MySQL/MariaDB recommended
```

## Configuration

```text
config/database_policy.php
```

Environment variables:

```text
DATABASE_PRODUCTION_DRIVER=mysql
DATABASE_ALLOW_SQLITE_LOCAL=true
DATABASE_ENFORCE_MYSQL_PRODUCTION=true
```

## Operational tools

```bash
php tools/diagnose-database-connection.php
php tools/assert-production-database.php
```

## Security / PCI-aware handling

Database diagnostics must not print passwords, full DSNs, SMTP passwords, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, provisioning URIs or QR data.
