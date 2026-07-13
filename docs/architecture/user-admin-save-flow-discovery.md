# Phase 1.16 - UserAdminController Save Flow Discovery

Phase 1.15 verified that `AdminUserSavePipeline` can generate SQL-safe admin user core writes. However, diagnostics showed `UserAdminController` does not contain a simple `save()` or `update()` repository call shape.

This phase therefore inspects the real controller save flow before applying a mutation patch. It writes a focused report showing controller methods, POST/body access, `new AdminUser(...)` construction, repository references and SQL-related repository lines.

## Decision

Do not patch the controller until the real save method and persistence call are identified from the inspection report.
