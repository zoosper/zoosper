# CLI `.env` loading

Use the shared bootstrap in CLI tools:

```php
$basePath = require __DIR__ . '/bootstrap.php';
```

Then read config normally:

```php
$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
```

Verify DB alignment:

```bash
php tools/diagnose-env-database.php
php tools/diagnose-database-connection.php
```

Expected when `.env` has `DB_CONNECTION=mysql` and MySQL is reachable:

```text
driver: mysql
configured_connection: mysql
```

Do not print or commit database passwords, SMTP passwords, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, provisioning URIs or QR data.
