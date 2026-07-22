# Role Admin Render Integration Contract

This document defines the source-level integration boundary for replacing `RoleAdminController` inline markup with Latte template rendering.

## Current phase scope

This phase is readiness-only. It does not rewrite `RoleAdminController`.

## Required existing assets

The render integration phase should require these templates to exist before controller changes:

```text
app/zoosper-core/views/admin/roles/index.latte
app/zoosper-core/views/admin/roles/form.latte
```

## Controller integration goal

The implementation phase should change `RoleAdminController` so it prepares data and delegates HTML rendering to the existing view/template layer rather than directly assembling large HTML strings.

## Source boundary rules

The controller may keep:

- request parsing;
- repository/service calls;
- redirects;
- status/error collection;
- CSRF token preparation;
- template selection and template data preparation.

The controller should not keep:

- long heredoc HTML blocks;
- table/form markup assembled inline;
- view-only loops that belong in Latte;
- HTML escaping responsibilities that templates can handle.

## Template data contract

### Role list template

Template target:

```text
admin/roles/index.latte
```

Expected data should include, as applicable:

- `roles`;
- `messages`;
- `csrfToken`;
- `createUrl`;
- `editUrlBase`;
- `deleteUrlBase`.

### Role form template

Template target:

```text
admin/roles/form.latte
```

Expected data should include, as applicable:

- `role`;
- `permissions`;
- `selectedPermissions`;
- `errors`;
- `csrfToken`;
- `actionUrl`;
- `cancelUrl`.

## Safety checks for implementation phase

After actual controller integration, add or update Pest coverage to verify:

1. role admin routes still have middleware permission coverage;
2. role admin forms still expose CSRF tokens;
3. `RoleAdminController.php` no longer contains large inline form/table markup;
4. the role list and form templates remain present;
5. the full Pest suite remains green.

## Non-goals

- No route path changes.
- No permission model changes.
- No repository or schema changes.
- No generated `var/reports` artefacts committed by default.
