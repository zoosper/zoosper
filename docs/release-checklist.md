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

## 0.2 starter experience
- Run `php bin/zoosper starter:install` twice and verify the second run retains the Site and Pages.
- Verify `/` and `/about` render through the default starter theme.
- Verify the application-owned session path and starter-theme files pass `release:check`.
