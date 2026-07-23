# Role Admin Latte Migration Closeout

This document records the final closeout criteria after moving role-admin markup from `RoleAdminController` into view partials.

## Completion criteria

Phase 1.38 can be closed when all of the following are true:

1. `RoleAdminController.php` exists.
2. `RoleAdminController.php` contains the view seam `renderRoleView()`.
3. `RoleAdminController.php` no longer owns large inline role-admin markup.
4. The role admin views exist:

```text
app/zoosper-admin/resources/views/admin/roles/index.php
app/zoosper-admin/resources/views/admin/roles/form.php
app/zoosper-admin/resources/views/admin/roles/permission-tree.php
app/zoosper-admin/resources/views/admin/roles/user-assignment.php
```

5. The views own the expected `<table`, `<form`, `<input`, and `<label` markup.
6. Full Pest passes.
7. The strict closeout gate passes:

```bash
php8.5 tools/audit-role-admin-latte-closeout.php --enforce-closed
```

## Ownership audit

Run:

```bash
php8.5 tools/audit-role-admin-view-ownership.php
```

The audit writes:

```text
var/reports/role-admin-view-ownership.txt
var/reports/role-admin-view-ownership.log
```

The log should include:

```text
VIEW_OWNERSHIP_ERRORS 0
CONTROLLER_MARKUP clean
```

## Commit hygiene

Generated `var/reports` artefacts should not normally be committed.
