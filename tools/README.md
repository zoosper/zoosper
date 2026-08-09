# Zoosper tools

Only active operational, verification, release and developer utilities belong in this directory. Completed migration/apply scripts are removed after their resulting code and tests are committed.

## Current tools

- `audit-module-package-readiness.php`
- `bootstrap.php`
- `cleanup-expired-rate-limit-buckets.php`
- `gate.php`
- `install-git-hooks.php`
- `site-lookup.php`
- `verify-latte-template-engine.php`
- `verify-module-dependencies.php`
- `verify-service-providers.php`

Durable tools are declared in `config/durable-tools.php`; Composer and source references provide the remaining dependency roots.
