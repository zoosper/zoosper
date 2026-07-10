# CLI says SQLite while the app is expected to use MySQL

## Symptom

```bash
php tools/diagnose-database-connection.php
```

prints:

```text
driver: sqlite
configured_connection: sqlite
```

but the developer expects MySQL/MariaDB.

## What it means

The CLI runtime is loading SQLite configuration. Browser/PHP-FPM and CLI can differ when:

- `.env` is not loaded by CLI tools
- `DB_CONNECTION` is not exported in the shell
- `config/database.php` defaults to sqlite
- PHP-FPM has different server-level environment variables from the SSH shell

## Safe diagnostic command

```bash
php tools/diagnose-env-database.php
```

This prints environment and config values visible to CLI, but redacts passwords.

## Quick checks

```bash
grep -R "DB_CONNECTION\|database.default\|sqlite\|mysql" -n .env config/database.php config/*.php
php -r "var_dump(getenv('DB_CONNECTION'), getenv('DB_DATABASE'));"
```

## Temporary CLI override test

```bash
DB_CONNECTION=mysql php tools/diagnose-env-database.php
```

If that switches the active PDO driver to MySQL, then the issue is environment loading/exporting rather than database code.

## PCI-aware note

Do not paste or commit database passwords. Redact DB_PASSWORD, SMTP_PASSWORD, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, provisioning URIs and QR data.
