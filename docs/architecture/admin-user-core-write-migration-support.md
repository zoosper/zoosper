# Phase 1.15 - AdminUser Core Write Migration Support

This phase adds the final support layer before migrating `UserAdminController`.

## Components

- `AdminUserCoreWriteSqlBuilder` builds SQL-safe `UPDATE admin_users` statements from field-definition-approved core write data.
- `AdminUserSavePipeline` provides a thin facade for controllers: create data, save context, and SQL-safe update statements.
- `diagnose-user-admin-controller-save-flow.php` reports whether the controller/repository are already using the new pipeline.

## Why this phase exists

Directly modifying `UserAdminController` has previously caused regressions. This phase verifies the save pipeline can produce safe SQL before controller migration.
