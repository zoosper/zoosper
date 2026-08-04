# Grid bulk-action host adapters

The protected HTTP boundary now has reusable callable adapters for CSRF validation, authenticated permission checks and audit-infrastructure readiness. The adapters let the host application delegate to its existing services without introducing dependencies from `zoosper-admin-grid` to Auth, Admin, Page or Store Orders.

`GridBulkExecutionResultAdapter` converts successful feature results to an application-relative 303 redirect and failures to a non-redirecting 422 result. Absolute, protocol-relative and malformed redirect paths are rejected.

These adapters do not expose a route or action. An integration phase must still bind the existing concrete CSRF, authenticated-user permission and audit services, and feature executors must re-authorise selected records and write actual audit events.
