# Troubleshooting

- Run `php bin/zoosper cache:clear` when compiled module discovery is stale.
- Run `php bin/zoosper module:manifest:status` to inspect manifest health.
- Run `php bin/zoosper release:check` for runtime prerequisite failures.
- Check `var/log` for application and exception logs.
- Confirm `var/cache` and `var/log` are writable.
- Resolve duplicate module identities using the paths reported by the collision exception.
- Verify module asset URLs when an Admin feature renders without its CSS or JavaScript.
- Confirm the configured database and site-domain mapping when routing fails.
