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
