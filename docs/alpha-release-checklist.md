# Alpha release checklist

- [ ] Clean tracked worktree and reviewed changelog
- [ ] Locked Composer installation succeeds
- [ ] Strict Composer validation and dependency audit pass
- [ ] JavaScript syntax and repository gate pass
- [ ] Fresh database migration succeeds
- [ ] Full Pest suite passes
- [ ] Module manifest compiles and is fresh
- [ ] `php bin/zoosper release:check` passes
- [ ] Admin login, dashboard, Pages, Media, Settings, Sites and Themes smoke-tested
- [ ] API health and frontend page rendering smoke-tested
- [ ] Production debug disabled and runtime directories writable
- [ ] Database and uploaded-file rollback point recorded

Psalm is advisory for this alpha and must remain visible in CI.
