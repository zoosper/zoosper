# Role Admin Template Contract

This document defines the target contract for extracting `RoleAdminController` markup into Latte templates.

## Purpose

The Phase 1.38 goal is to make `RoleAdminController` a thin request handler. The controller should prepare data, call repositories/services, handle redirects/errors, and then delegate HTML rendering to Latte templates.

## Target templates

The implementation phase should create templates similar to:

```text
app/zoosper-core/views/admin/roles/index.latte
app/zoosper-core/views/admin/roles/form.latte
```

If the current view path conventions differ, keep the repository convention and document the final path in this file.

## Controller responsibilities after extraction

The controller should keep responsibility for:

- reading request input;
- invoking repositories/services;
- preserving route and middleware permission semantics;
- preserving CSRF token availability;
- selecting the correct template and template data;
- redirecting after successful mutations.

## Template responsibilities after extraction

Latte templates should own:

- role list HTML;
- role form HTML;
- error/message display markup;
- CSRF hidden fields where the controller/template data provides a token;
- links/buttons that match existing route URLs.

## Non-goals

- Do not change role permissions in this template extraction.
- Do not change route names or route paths unless a separate route migration phase explicitly does so.
- Do not move business logic into templates.
- Do not commit generated `var/reports` artefacts by default.

## Implementation sequence for the next phase

1. Run `php8.5 tools/plan-role-admin-latte-extraction.php`.
2. Review the generated report.
3. Add the target Latte templates.
4. Replace controller inline HTML rendering with template rendering.
5. Add a regression test that `RoleAdminController.php` no longer contains large inline HTML/heredoc markup.
6. Run the full Pest suite.
