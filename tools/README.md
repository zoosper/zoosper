# Zoosper tools

The root tools directory contains executable utilities that are dependency roots of Composer, CI, hooks, runtime recovery guidance or the durable tool registry. Historical phase, patch, readiness and closure tools without a live dependency root are removed.

## Durable registry

Durable tools are declared in `config/durable-tools.php`. The strict gate validates that registry and the on-disk tool set.

## Retained dependency closure

- `tools/apply-admin-form-config-aggregator-layered-loader.php`
- `tools/apply-admin-form-config-layered-loader.php`
- `tools/apply-composer-internal-package-stability.php`
- `tools/apply-composer-local-package-repositories.php`
- `tools/apply-rate-limit-admin-login-policy.php`
- `tools/apply-rate-limit-admin-middleware-hook.php`
- `tools/apply-role-admin-latte-cutover.php`
- `tools/apply-role-admin-markup-view-cutover.php`
- `tools/apply-site-lookup-service-binding.php`
- `tools/audit-module-package-readiness.php`
- `tools/bootstrap.php`
- `tools/cleanup-expired-rate-limit-buckets.php`
- `tools/gate.php`
- `tools/install-git-hooks.php`
- `tools/site-lookup.php`
- `tools/verify-latte-template-engine.php`
- `tools/verify-module-dependencies.php`
- `tools/verify-service-providers.php`

## Policy

New one-off phase scripts should live outside the repository or be removed before the phase is committed. A root tool must be referenced by an active workflow or declared durable.
