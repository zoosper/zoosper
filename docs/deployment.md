# Deployment

Deploy a clean tracked checkout, install locked Composer dependencies, provide production environment configuration and ensure runtime directories are writable.

Run `php bin/zoosper deploy`, followed by `php bin/zoosper release:check`. The deploy command applies migrations, compiles the module manifest and verifies freshness.

Production must use `APP_ENV=production`, `APP_DEBUG=false`, secure secrets and suitable session-cookie settings. Record database and uploaded-Media rollback points before deployment.
