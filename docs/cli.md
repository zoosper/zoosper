# Command line

Run `php bin/zoosper list` for the current command inventory.

Core operational commands include:
- `migrate`: Execute pending database migrations across all registered modules.
- `compile`: Compile the module manifest for production performance.
- `cache:clear`: Clear the compiled module manifest cache.
- `module:manifest:status`: Display discovery vs compiled manifest status.
- `module:manifest:check`: Validate compiled manifest against current module definitions.
- `deploy`: Execute atomic deployment pipeline (migrate, compile, manifest verify).
- `security:generate-secrets`: Generate or audit cryptographically strong secrets (`APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY`, `RATE_LIMIT_IDENTITY_SALT`, `CACHE_ENCRYPTION_KEY`) with optional `--write`, `--check`, or `--force` flags.
- `release:check`: Run pre-flight release readiness verification.
- `schema:foreign-keys:status` / `schema:foreign-keys:apply`: Inspect and apply declarative foreign key constraints.
- `version`: Display CMS release version and channel.

Module-owned commands also contribute feature-specific workflows, such as `admin:create` (user creation), `site:create`, and `page:create`.

Recovery commands including `help`, `list`, `compile`, `cache:clear`, and `security:generate-secrets` remain operational without an active database connection. Database-backed commands resolve PDO only when executed.
