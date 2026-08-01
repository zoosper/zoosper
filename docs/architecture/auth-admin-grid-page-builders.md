# Auth admin Grid page builders

Phase 4Q adds complete, framework-neutral view models and page builders for Admin
Users and Roles. Each builder resolves one per-admin workspace state, queries rows
with that exact criteria, and renders only the Grid body so the legacy filter bar is
not duplicated.

No controller, route, permission, CSRF, password, 2FA, role-assignment or permission-
assignment behaviour changes in this phase. The next phase can register and boot-test
the object graph before the live index cutover.
