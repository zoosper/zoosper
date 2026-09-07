# Configuration

Zoosper loads module defaults beneath project configuration. Environment variables are used for deployment-specific values and secrets. Values supplied by the process manager or container take precedence; existing `$_ENV` values follow, and `.env` fills only missing keys.

## Configuration groups

- **Application & Security**: `config/app.php` and `config/security.php` define environment mode (`local`, `development`, `testing`, `staging`, `production`), `APP_DEBUG`, CSP directives, and security headers.
- **Secrets & Encryption**: `APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY`, `RATE_LIMIT_IDENTITY_SALT`, and `CACHE_ENCRYPTION_KEY` can be generated and audited using `php bin/zoosper security:generate-secrets`.
- **Admin & Session Lifecycle**: `config/admin.php` controls administrative base path, password complexity, `ADMIN_SESSION_IDLE_TIMEOUT` (idle inactivity timeout in seconds), and `ADMIN_SESSION_ABSOLUTE_LIFETIME` (maximum total session duration in seconds).
- **Database & Persistence**: `config/database.php` configures PDO connections (`sqlite`, `mysql`). In staging and production, strict database driver policies are enforced.
- **Cache & Storage**: `config/cache.php` and `config/page_cache.php` govern cache drivers (`file`, `redis`) and full-page caching.

`config/version.php` is the central default CMS version source. `CMS_VERSION` is an optional deployment override.

Never commit `.env`, credentials, encryption keys or production connection strings.

## Admin account lockout

```dotenv
ADMIN_ACCOUNT_LOCKOUT_MAX_ATTEMPTS=5
ADMIN_ACCOUNT_LOCKOUT_SECONDS=900
```

These variables configure temporary per-account lockout for known active Admin users. The first value is the failed-password threshold and the second is the lock duration in seconds. The shipped example uses five attempts and 900 seconds.

Account lockout and request rate limiting are separate controls. Account lockout persists failure state for a known active Admin identity. The Admin login rate limiter protects request volume using its configured email/IP identity. Either control may reject a request independently.

Temporary lockout does not change the Admin user's active/inactive status. Public login output remains neutral and does not reveal lockout state or expiry.
