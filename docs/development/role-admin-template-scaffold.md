# Role Admin Template Scaffold

This document records the initial Latte template scaffold for the Phase 1.38 RoleAdminController migration.

## Template files

```text
app/zoosper-core/views/admin/roles/index.latte
app/zoosper-core/views/admin/roles/form.latte
```

## Scope of this phase

This phase adds template files and guards only. It does not yet rewrite `RoleAdminController` to render them.

## Template data assumptions

The implementation phase should pass conservative data to these templates:

### index.latte

Expected template data:

- `roles`: iterable list/array of role records or role objects;
- `messages`: optional list of status/error messages;
- `csrfToken`: optional token for forms/actions where needed;
- `createUrl`: optional URL for the create role action;
- `editUrlBase`: optional URL prefix/base for editing roles;
- `deleteUrlBase`: optional URL prefix/base for deleting roles.

### form.latte

Expected template data:

- `role`: current role record/object or `null` for create mode;
- `permissions`: iterable list/array of permission records;
- `selectedPermissions`: iterable list/array of selected permission identifiers;
- `errors`: optional list of validation errors;
- `csrfToken`: CSRF token for mutation forms;
- `actionUrl`: form submission URL;
- `cancelUrl`: return URL.

## Rules

- Templates own markup only.
- Controllers/services own business logic.
- Do not change route paths or permission semantics in the scaffold phase.
- Do not commit generated `var/reports` artefacts by default.
