# RoleAdminController Helper Pattern Discovery

This document records the final helper-pattern discovery step for Phase 1.38.

## Why this phase exists

The public method excerpts show the final cutover shape clearly:

- `index()` contains role table markup directly;
- `createForm()` delegates to `form('/admin/roles/create')`;
- `editForm()` delegates to `form('/admin/roles/edit?id=...', $role)`;
- both form routes therefore depend on the private/helper `form()` method;
- the response/layout seam depends on the private/helper `html()` method.

To close Phase 1.38 safely, the next implementation patch must know the exact `form()` and `html()` bodies before it removes inline form/table/input/heredoc markup from the controller.

## Tool

```text
tools/discover-role-admin-helper-pattern.php
```

## Output

The tool writes:

```text
var/reports/role-admin-helper-pattern.txt
var/reports/role-admin-helper-pattern.log
var/reports/role-admin-helper-pattern-source/
```

The source directory includes helper method excerpts for:

- `html()`;
- `form()`;
- `e()`;
- `currentAdminUser()`;
- `roleFromRequest()`;
- the public wrappers `index()`, `createForm()`, and `editForm()`.

## Next phase rule

After this phase, the next implementation should be a source-specific cutover patch that either:

1. adds a safe recognised pattern to `tools/apply-role-admin-latte-cutover.php`; or
2. directly updates `RoleAdminController.php` to delegate role list/form rendering to templates and removes large inline markup from controller source.
