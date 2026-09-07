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

## Admin user cannot sign in after repeated password failures

1. Confirm whether the Admin user is active. Inactive status and temporary account lockout are separate conditions; unlocking does not activate an inactive user.
2. Check `ADMIN_ACCOUNT_LOCKOUT_MAX_ATTEMPTS` and `ADMIN_ACCOUNT_LOCKOUT_SECONDS` in the deployment environment. The shipped example is five failures and 900 seconds.
3. Distinguish account lockout from the Admin request rate limiter. A rate-limited request may return HTTP 429 with `Retry-After`; account lockout keeps the public password response neutral with HTTP 422.
4. For an authorised operational recovery, sign in as an Admin with `user.manage`, open the affected Admin User edit page, review the failed-attempt count and UTC expiry, then use **Unlock account**. The POST action requires a valid CSRF token and redirects back to the edit page with HTTP 303.
5. If the user also needs a new password, complete the Admin forgot-password flow. Only a successful reset clears lockout state; invalid, expired, consumed, superseded, mismatched, or weak reset attempts do not.
6. Confirm the audit log contains `admin_user.account_unlocked` for a manual clear. The event identifies the actor and target Admin user but intentionally excludes passwords, hashes, reset tokens, lock expiry, IP addresses, user agents, and session identifiers.

If the lock has expired, the next authentication check removes stale lockout state automatically. A successful login below the threshold also clears accumulated failures.
