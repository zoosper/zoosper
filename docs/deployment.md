# Deployment

Deploy a clean tracked checkout, install locked Composer dependencies, provide production environment configuration and ensure runtime directories are writable. For immutable application delivery, run `php8.5 tools/build-production-artifact.php --output=/absolute/output/path` from a clean worktree. The builder uses an explicit runtime allow-list, installs production dependencies, materialises first-party path packages, compiles and verifies the module manifest, rejects development-only content and symlinks, emits `RELEASE-MANIFEST.json`, and writes a SHA-256 sidecar after isolated extraction checks.

Run `php bin/zoosper deploy`, followed by `php bin/zoosper release:check`. The deploy command applies migrations, compiles the module manifest and verifies freshness.

Production must use `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE=true`, `RATE_LIMIT_ENABLED=true`, `RATE_LIMIT_MODE=enforce`, and a strong `RATE_LIMIT_IDENTITY_SALT`. Staging enforces the same controls. Unknown or empty `APP_ENV` values fail boot. Process-manager and container values take precedence over `.env`. Record database and uploaded-Media rollback points before deployment.

When `CACHE_DRIVER=redis`, staging and production also require a non-empty, non-placeholder `CACHE_REDIS_PASSWORD` and a strong dedicated `CACHE_ENCRYPTION_KEY`. The current Marko Redis boundary supports password authentication without a separate ACL username. File-cache deployments do not require Redis credentials or the Redis signing key. Cache service construction remains lazy, and a later Redis availability failure remains isolated from frontend page rendering.
