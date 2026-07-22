# RoleAdminController Latte Closeout Criteria

This document defines the criteria for closing Phase 1.38.

## Completion criteria

Phase 1.38 may be treated as closed only when all of the following are true:

1. `RoleAdminController.php` exists and remains route-compatible.
2. The role admin list template exists:

```text
app/zoosper-core/views/admin/roles/index.latte
```

3. The role admin form template exists:

```text
app/zoosper-core/views/admin/roles/form.latte
```

4. `RoleAdminController.php` no longer owns large role-admin form/table/heredoc HTML markup.
5. CSRF handling remains represented in the controller/template flow.
6. Role/permission source signals remain represented.
7. Full Pest remains green.

## Closeout gate

Run:

```bash
php8.5 tools/audit-role-admin-latte-closeout.php
```

The report writes:

```text
var/reports/role-admin-latte-closeout.txt
var/reports/role-admin-latte-closeout.log
```

The log contains one of:

```text
CLOSEOUT_STATUS closed
CLOSEOUT_STATUS open
```

## Strict mode

Run this only when expecting the migration to be complete:

```bash
php8.5 tools/audit-role-admin-latte-closeout.php --enforce-closed
```

Strict mode exits non-zero if Phase 1.38 is still open.

## Commit hygiene

Generated `var/reports` artefacts should not normally be committed.
