# Zoosper Session

Zoosper-owned adapter for swappable session infrastructure. The root project depends on `zoosper/session`; this package owns the concrete third-party dependency so the storage driver can be overridden, replaced, or removed without coupling Zoosper Core or Auth to Marko implementation classes.

## Responsibilities

- Own the concrete session-storage integration boundary for Zoosper.
- Bind native PHP `SessionHandlerInterface` to the selected driver.
- Supply Marko's typed `SessionConfig` from Zoosper's shared configuration repository.
- Store session payloads in an application-owned location instead of the host-managed PHP session directory.
- Preserve compatibility with existing `$_SESSION` consumers, including Admin authentication, CSRF tokens, flash messages, pending two-factor state, session regeneration, logout, and Admin idle tracking.
- Keep `SESSION_LIFETIME_SECONDS` authoritative for cookie and storage lifetime while leaving `ADMIN_SESSION_IDLE_TIMEOUT` as an independent authentication-inactivity policy.

## Architecture

The package is a native Composer package with type `zoosper-module`. The root project requires `zoosper/session`; this module requires `marko/session-file` 0.8.5. Marko's transitive `marko/session` package provides `SessionConfig` and session contracts.

`config/settings/session.php` owns the `session.*` configuration consumed by Marko. `config/services.php` creates `FileSessionHandler` through `SessionConfig` and publishes it as native `SessionHandlerInterface`. Zoosper Core resolves only the native interface and registers the selected handler before `session_start()`.

The default storage path is `var/sessions`. Relative paths are resolved by Marko from the project working directory, and absolute or stream-wrapper paths remain supported by the driver.

## Configuration

- `SESSION_LIFETIME_SECONDS`: absolute session payload and cookie lifetime in seconds. The module converts this to Marko's minute-based configuration while Core retains the exact second value for PHP session garbage collection and the browser cookie.
- `ADMIN_SESSION_IDLE_TIMEOUT`: independent maximum inactivity for authenticated or pending two-factor Admin state.
- `SESSION_STORAGE_PATH`: session payload directory; defaults to `var/sessions`.
- `SESSION_NAME`: browser cookie name; defaults to `ZOOSPERSESSID`.
- `SESSION_SECURE`: whether the browser cookie is HTTPS-only.
- `SESSION_SAMESITE`: cookie SameSite policy.

## Dependencies

Production dependencies are declared in this module's `composer.json`:

- PHP 8.5 or newer.
- `zoosper/core` for the container and application bootstrap contracts.
- `marko/session-file` 0.8.5 as the current concrete file driver.
- `marko/session` 0.8.5 transitively through the file driver.

The root `composer.json` must not directly require Marko session packages. This restriction is covered by `ZoosperSessionMarkoAdapterTest`.

## Extension points

A later Zoosper module may replace the `SessionHandlerInterface` binding with another native-compatible handler, including a database or Redis-backed implementation. Core, Auth, CSRF, flash, and two-factor code do not need to change when the binding changes.

Marko-specific construction must remain inside `zoosper/session`. Do not import `FileSessionHandler`, `SessionConfig`, or other concrete Marko session classes into `zoosper-core` or `zoosper-auth`.

## Testing

Run the package and integration tests:

```bash
php8.5 vendor/bin/pest app/zoosper-session/tests app/zoosper-core/tests/Unit/Http/ZoosperSessionMarkoAdapterTest.php app/zoosper-core/tests/Unit/Http/ApplicationSessionLifetimeTest.php
```

Run the full repository suite:

```bash
zcomposer test
```

Run the standard quality gate:

```bash
php8.5 tools/gate.php
```

Check formatting and whitespace:

```bash
git diff --check
```

## Operational notes

The PHP-FPM user must be able to create and write the configured storage directory. Marko creates the session directory with owner-only mode `0700` and session files with mode `0600`.

After enabling the module or changing session-cookie attributes, restart PHP-FPM and issue a fresh `ZOOSPERSESSID` cookie. Existing sessions stored in the host PHP directory are intentionally not migrated.

For HTTPS Admin installations, configure `SESSION_SECURE=true`. Keep `SESSION_LIFETIME_SECONDS` and `ADMIN_SESSION_IDLE_TIMEOUT` aligned when the desired absolute and inactivity limits are the same.

Do not delete `var/sessions` during routine deployments unless intentionally terminating every active session. The directory is runtime state and must remain excluded from source control.
