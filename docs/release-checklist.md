# Release checklist

- Clean worktree and reviewed changelog
- Locked Composer installation, validation and audit
- Full Pest suite (including MySQL and SQLite integration test legs)
- JavaScript syntax and strict repository gate
- Static analysis baseline status review (Psalm zero-baseline drive or reviewed residual)
- Disposable fresh-install smoke and containerized production boot verification
- Foreign key referential integrity status check (`schema:foreign-keys:status`)
- Secret presence, strength, and placeholder validation (`APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY`, `RATE_LIMIT_IDENTITY_SALT`)
- Content Security Policy (CSP) enforcement verification (`report_only => false`)
- Media derivative queue worker health and pre-decode limit checks
- Module asset pipeline adversarial path-normalisation and extension-allow-list tests
- Module manifest compilation and freshness (`module:manifest:check`)
- Release checks under production-safe configuration (`release:check`)
- Live frontend, API health and Admin login checks
- Authenticated Admin smoke journey
- Database and Media rollback points
- Annotated immutable release tag

## Starter experience
- Run `php8.5 bin/zoosper starter:install` twice and verify the second run retains the Site and Pages.
- Verify `/` and `/about` render through the default starter theme.
- Verify the application-owned session path and starter-theme files pass `release:check`.

## Reusable final tag gate

```bash
RELEASE_TAG=v0.3.0-alpha.4
NEXT_VERSION=0.3.0-alpha.4
```
- Confirm `php8.5 bin/zoosper version` reports the release version represented by `RELEASE_TAG`.
- Confirm Admin and API health expose the same authoritative version.
- Run the full test suite, quality gate, fresh-install smoke, compile, manifest check, release check, and documentation build.
- Record manual browser evidence for login/logout, application-owned sessions, Home/About rendering, Page revision restore, Menu rendering, and Media lifecycle.
- Commit and push the release identity, require a clean working tree, then create annotated tag defined by `RELEASE_TAG`.
