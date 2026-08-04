# Admin Grid bulk-action HTTP boundary

`zoosper-admin-grid` now provides a framework-neutral protected POST coordinator above the `zoosper-grid` dispatcher. The coordinator validates CSRF through a host adapter, parses only POST form input, resolves the registered definition, checks its permission through a host adapter, requires action-bound confirmation, validates the selection maximum and fails closed when required audit infrastructure is unavailable.

The boundary contains no Page, Store Orders, repository or remote API dependency. It does not read `$_POST` directly and does not expose an HTTP route. Feature integration must still authenticate the administrator, supply adapters, re-authorise every selected record inside the executor, use appropriate transactions and write the actual audit event.

No new Pages mutation is visible in this phase.
