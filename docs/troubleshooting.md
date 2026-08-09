# Troubleshooting

- Use `php bin/zoosper cache:clear` when module discovery or compiled-manifest state is stale.
- Resolve duplicate module identities by removing the stale copy named in `DuplicateModuleException`.
- Check `var/log/exception.log` for server-side exception details.
- Verify module asset URLs when Admin feature styling is missing.
- Confirm `var/cache` and `var/log` are writable.
- Run `php bin/zoosper release:check` for a concise blocker list.
- Keep `APP_DEBUG` disabled in production.
