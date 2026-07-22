# RoleAdminController Latte Migration

Phase 1.38 migrates `RoleAdminController` towards the thin-controller/view-layer pattern.

## Why this phase exists

The controller cleanup roadmap requires admin controllers to act as request handlers only. Embedded HTML/heredoc output should move into Latte templates/views so future modules can extend or override rendering without editing core controller code.

## Current migration rule

This readiness phase is intentionally read-only. It documents the target and adds audit coverage before rewriting controller output.

## Target outcome

A completed RoleAdminController migration should:

1. keep routing and middleware permission semantics unchanged;
2. keep CSRF token generation and validation behaviour unchanged;
3. preserve role CRUD actions and redirects;
4. move embedded HTML into role-admin Latte templates/views;
5. keep business rules in services/repositories, not templates;
6. make future third-party extension points easier by avoiding controller-owned markup.

## Expected template targets

The exact template filenames can be adjusted during implementation, but the migration should aim for role-admin templates such as:

```text
app/zoosper-core/views/admin/roles/index.latte
app/zoosper-core/views/admin/roles/form.latte
```

## Recommended implementation sequence

1. Run the read-only audit:

```bash
php8.5 tools/audit-role-admin-latte-readiness.php
```

2. Inspect the report under `var/reports/`.
3. Add Latte template files for role list and form rendering.
4. Replace controller-owned HTML rendering with template rendering.
5. Keep route/middleware/CSRF tests green.
6. Add regression coverage that RoleAdminController no longer contains heredoc HTML.

## Non-goals for this readiness phase

- No controller rewrite yet.
- No route contract changes.
- No permissions changes.
- No generated `var/reports` files committed by default.
