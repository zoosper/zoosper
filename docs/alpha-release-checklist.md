# Alpha release checklist

- [x] Clean tracked worktree and reviewed changelog
- [x] Locked Composer installation succeeds
- [x] Strict Composer validation and dependency audit pass
- [x] JavaScript syntax and repository gate pass
- [x] Disposable SQLite migration from zero succeeds and reruns idempotently
- [x] Full Pest suite passes
- [x] Module manifest compiles and is fresh
- [x] `php bin/zoosper release:check` passes
- [~] Critical Admin/API route and asset inventory is automated; authenticated browser journeys remain manual
- [~] API health route inventory is automated; live API and frontend rendering remain manual
- [x] Production debug disabled and runtime directories writable
- [x] Database and uploaded-file rollback point recorded

Psalm is advisory for this alpha and must remain visible in CI.
