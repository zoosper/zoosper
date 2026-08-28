# Deployment

Deploy a clean tracked checkout, install locked Composer dependencies, provide production environment configuration and ensure runtime directories are writable.

Run `php bin/zoosper deploy`, followed by `php bin/zoosper release:check`. The deploy command applies migrations, compiles the module manifest and verifies freshness.

Production must use `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE=true`, `RATE_LIMIT_ENABLED=true`, `RATE_LIMIT_MODE=enforce`, and a strong `RATE_LIMIT_IDENTITY_SALT`. Staging enforces the same controls. Unknown or empty `APP_ENV` values fail boot. Process-manager and container values take precedence over `.env`. Record database and uploaded-Media rollback points before deployment.
