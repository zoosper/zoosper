# RoleAdminController Cutover Plan

This document defines the final implementation checklist for replacing `RoleAdminController` inline markup with role admin Latte template rendering.

## Required preconditions

These assets should exist before the controller is rewritten:

```text
app/zoosper-core/views/admin/roles/index.latte
app/zoosper-core/views/admin/roles/form.latte
docs/development/role-admin-render-integration.md
tools/audit-role-admin-render-integration.php
```

## Cutover objective

Make `RoleAdminController` a thin request handler by moving role list/form markup into the `index.latte` and `form.latte` templates while preserving current behaviour.

## Implementation checklist

1. Run:

```bash
php8.5 tools/plan-role-admin-controller-cutover.php
```

2. Review `var/reports/role-admin-controller-cutover.txt`.
3. Identify the current app view/rendering service convention from existing controllers.
4. Inject or use the existing renderer in `RoleAdminController` using the same project convention.
5. Replace controller-owned role list markup with rendering of:

```text
admin/roles/index.latte
```

6. Replace controller-owned role form markup with rendering of:

```text
admin/roles/form.latte
```

7. Preserve all existing route URLs, redirects, flash/message handling, permission checks, and CSRF token behaviour.
8. Add/update tests so `RoleAdminController.php` no longer contains large `<form`, `<table`, or heredoc HTML blocks.
9. Run full Pest.

## Data handoff guidance

### List template data

- `roles`
- `messages`
- `csrfToken`
- `createUrl`
- `editUrlBase`
- `deleteUrlBase`

### Form template data

- `role`
- `permissions`
- `selectedPermissions`
- `errors`
- `csrfToken`
- `actionUrl`
- `cancelUrl`

## Safety rules

- Do not change route paths in this cutover.
- Do not change ACL/permission names.
- Do not change CSRF middleware behaviour.
- Do not move business logic into Latte templates.
- Do not commit generated `var/reports` files by default.
