# Getting started

## Requirements

Zoosper requires PHP 8.5 or newer, Composer 2, PDO and a supported database driver. The web server document root must be `public/`. Runtime processes require write access to `var/cache` and `var/log`.

## Install

1. Copy `.env.example` to `.env`.
2. Configure the application URL, database and environment secrets. The example targets local HTTP, so `SESSION_SECURE=false` and rate limiting is disabled. Before enabling rate limiting, generate a stable `RATE_LIMIT_IDENTITY_SALT`; staging and production require Secure cookies and enforced rate limiting.
3. Run `composer install`.
4. Run `php bin/zoosper migrate`.
5. Run `php bin/zoosper compile`.
6. Use `php bin/zoosper list` to discover the Admin and Site bootstrap commands.
7. Run `php bin/zoosper release:check`.

Start the local development server with `composer serve`; its router forwards non-file requests such as `/admin/login` to the front controller.

For a disposable verification installation, run `composer fresh-install:smoke`. This command uses a temporary SQLite database and does not modify the configured project database.

## Starter content
After migrations and Site setup, run `php bin/zoosper starter:install`. The command creates only a missing Site and missing published Home/About Pages. Existing records are retained, so rerunning it is safe.

## Admin password-reset configuration
Before using Admin password reset outside local development:

1. Set `APP_URL` to the trusted absolute HTTP or HTTPS application origin. The value must not contain credentials, a query, or a fragment. Reset links combine this origin with the configured Admin base path.
2. Configure working SMTP delivery with `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `SMTP_HOST`, `SMTP_PORT`, and any required SMTP authentication or encryption values.
3. Configure a strong stable `RATE_LIMIT_IDENTITY_SALT` and enable authentication rate limiting. Staging and production already require `RATE_LIMIT_ENABLED=true` and `RATE_LIMIT_MODE=enforce` at boot.
4. Tune `RATE_LIMIT_ADMIN_PASSWORD_RESET_MAX_ATTEMPTS` and `RATE_LIMIT_ADMIN_PASSWORD_RESET_WINDOW_SECONDS`. Defaults are 5 attempts in 900 seconds, bounded by the shared rate-limit configuration.
5. Run `php8.5 bin/zoosper migrate` so `admin_password_reset_tokens` exists, then run the normal compile and release checks.

The public flow is available from the **Forgot password?** link on the Admin sign-in page. Reset messages intentionally bypass Email Logs because the URL contains the single-use credential.
