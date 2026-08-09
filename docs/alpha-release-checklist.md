# Alpha release checklist

- [ ] Clean tracked worktree and reviewed changelog
- [ ] Locked Composer installation succeeds
- [ ] Strict Composer validation and dependency audit pass
- [ ] JavaScript syntax and repository gate pass
- [x] Disposable SQLite migration from zero succeeds and reruns idempotently
- [ ] Full Pest suite passes
- [ ] Module manifest compiles and is fresh
- [ ] `php bin/zoosper release:check` passes
- [~] Critical Admin/API route and asset inventory is automated; authenticated browser journeys remain manual
- [~] API health route inventory is automated; live API and frontend rendering remain manual
- [ ] Production debug disabled and runtime directories writable
- [ ] Database and uploaded-file rollback point recorded

Psalm is advisory for this alpha and must remain visible in CI.
