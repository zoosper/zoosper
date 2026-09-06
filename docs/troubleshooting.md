# Troubleshooting

- Run `php bin/zoosper cache:clear` when compiled module discovery is stale.
- Run `php bin/zoosper module:manifest:status` to inspect manifest health.
- Run `php bin/zoosper release:check` for runtime prerequisite failures.
- Check `var/log` for application and exception logs.
- Confirm `var/cache` and `var/log` are writable.
- Resolve duplicate module identities using the paths reported by the collision exception.
- Verify module asset URLs when an Admin feature renders without its CSS or JavaScript.
- Confirm the configured database and site-domain mapping when routing fails.

## Admin password reset
### No reset email arrives

Confirm SMTP is reachable and the active account email is correct. The public page always returns the same neutral message for unknown accounts, inactive accounts, rate-limit denial, and delivery failure, so the browser response intentionally does not identify the cause. Reset messages bypass Email Logs by design; inspect only secret-safe SMTP and application diagnostics and never copy a reset URL into logs or tickets.

### Reset link points to the wrong host or Admin path

Check `APP_URL` and the configured Admin base path. `APP_URL` must be an absolute HTTP or HTTPS origin without credentials, query, or fragment. Do not derive reset links from an untrusted request Host header.

### Forgot-password submission returns HTTP 419

The stateful public form requires the current CSRF token. Reload the form and submit the newly rendered token. Confirm application-owned session storage is writable and the browser retains the expected session cookie.

### Reset submission returns HTTP 422

The credential may be malformed, expired, consumed, superseded by a newer request, associated with an inactive account, or the new password may fail confirmation or canonical policy validation. Request a new link and use only the newest message.

### Valid requests stop issuing new credentials

Check the dedicated `admin.password_reset_request` rate-limit policy and its environment values. In enforce mode, denial deliberately returns the neutral HTTP response and must not supersede an already outstanding reset credential. Do not disable the shared salted rate-limit boundary in staging or production.

### Existing sessions stop working after a reset

This is expected. The Admin session guard stores a password-hash fingerprint. Successful reset changes the canonical password hash, so existing authenticated sessions fail validation on their next guard check and must sign in again.
