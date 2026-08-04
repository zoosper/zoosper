# Protected Grid bulk coordinator integration

`GridBulkHttpCoordinatorFactory` now composes the shared request parser, definition registry, dispatcher, CSRF adapter, permission adapter, audit readiness guard and confirmation guard. `GridBulkHostBindings` accepts the host application's existing checks as validated callables, keeping `zoosper-admin-grid` independent from Auth, Page and Store Orders.

The integration test exercises the complete protected path and proves that CSRF, permission, confirmation, audit readiness and selection maximums fail before feature execution. A successful path de-duplicates selected identities before delegation.

This phase does not register a production action, expose a route, mutate Pages or emit an audit event. The next feature phase can add `Publish selected` through a Page-owned executor only after its repository, action audit and domain-event semantics are verified.
