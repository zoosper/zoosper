# Dynamic Admin Runtime Contract

Admin route and menu manifests declare canonical `/admin` paths. Runtime controllers, templates, Grid workspaces and JavaScript configuration must consume `AdminUrlGenerator` or a module-owned helper backed by it.

Static assets remain under `/assets/admin`. Explicit constructor defaults and endpoint constants may retain `/admin` for backwards-compatible direct construction, but the container-built runtime must receive the canonical generator.

## Session idle timeout

`ADMIN_SESSION_IDLE_TIMEOUT` controls inactivity expiry in seconds and defaults to `7200`. `0` disables idle expiry. Invalid and negative configuration values fall back to the default.

The timeout applies to authenticated Admin sessions and pending two-factor challenges. Missing, malformed or future activity timestamps fail closed when protected session identity is present.
## Trust-transition CSRF lifecycle

A successful password check rotates the CSRF token before entering pending two-factor or fully authenticated state. Successful two-factor promotion rotates it again. Logout clears it. Invalid credentials and failed one-time codes do not rotate the token, so the current form can be corrected and resubmitted safely.

The Settings catalogue exposes `admin.session_idle_timeout` as read-only because the active guard is composed from environment configuration during application bootstrap.
## Session bootstrap and cookie policy

The HTTP `Application` is the sole production web-session bootstrap. It enables strict session identifiers, cookie-only transport, disables transparent session IDs, applies an HttpOnly cookie and validates SameSite to Lax, Strict or None. SameSite=None falls back to Lax when the cookie is not secure.

`SESSION_LIFETIME_SECONDS` is bounded to 300..604800 seconds and controls both PHP garbage collection and the browser cookie lifetime. Logout expires the browser cookie with the active cookie parameters before destroying the server session.
## Trusted proxy and client identity

Forwarded scheme and client-address headers are accepted only when `REMOTE_ADDR` is listed in the comma-separated `TRUSTED_PROXIES` environment variable. Direct requests use the validated peer address. Untrusted peers cannot force HTTPS detection or replace the client identity used by security logging and rate-limit hashing.

The current Admin login limiter remains disabled by default and report-only when enabled. Login email identity is normalised to lowercase before opaque salted hashing.

