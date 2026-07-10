# MySQL/MariaDB local development

Zoosper is now MySQL/MariaDB-first. For feature work involving schemas, modules, admin auth, 2FA, permissions or mail-related persistence, use a MySQL-family database rather than SQLite.

## Recommended `.env` shape

```text
APP_ENV=local
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zoosper
DB_USERNAME=zoosper
DB_PASSWORD=secret
DATABASE_PRODUCTION_DRIVER=mysql
DATABASE_ALLOW_SQLITE_LOCAL=true
DATABASE_ENFORCE_MYSQL_PRODUCTION=true
```

## Verify active connection

```bash
php tools/diagnose-database-connection.php
php tools/assert-production-database.php
php tools/diagnose-two-factor-schema.php
```

## Apply schema

```bash
php bin/zoosper migrate
php tools/diagnose-two-factor-schema.php
```

## Note

SQLite can still be used for quick local bootstrap checks if allowed, but missing SQLite tables should not block the MySQL-first roadmap.
