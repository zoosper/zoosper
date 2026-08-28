# Release checklist

- Clean worktree and reviewed changelog
- Locked Composer installation, validation and audit
- Full Pest suite
- JavaScript syntax and strict repository gate
- Disposable fresh-install smoke
- Module manifest compilation and freshness
- Release checks under production-safe configuration
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
RELEASE_TAG=v0.3.0-alpha.3
NEXT_VERSION=0.3.0-alpha.4-dev
```
- Confirm `php8.5 bin/zoosper version` reports the release version represented by `RELEASE_TAG`.
- Confirm Admin and API health expose the same authoritative version.
- Run the full test suite, quality gate, fresh-install smoke, compile, manifest check, release check, and documentation build.
- Record manual browser evidence for login/logout, application-owned sessions, Home/About rendering, Page revision restore, Menu rendering, and Media lifecycle.
- Commit and push the release identity, require a clean working tree, then create annotated tag defined by `RELEASE_TAG`.
