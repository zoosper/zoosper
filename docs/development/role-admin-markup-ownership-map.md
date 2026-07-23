# RoleAdminController Markup Ownership Map

This document records the final blocker map needed before closing Phase 1.38.

## Confirmed from helper-pattern discovery

The helper discovery confirmed:

- `index()` owns list/table rendering when it contains table markup;
- `createForm()` and `editForm()` delegate to `form()`;
- `form()` owns form/input/heredoc markup;
- `html()` is the `AdminLayout::render()` seam.

## Why this phase exists

The Phase 1.38 closeout gate checks the controller source for large inline markup signals. Removing only `index()` and `form()` may not be enough if other private helpers such as permission/user assignment helpers also own inline input or label markup.

This phase therefore scans all methods in `RoleAdminController.php`, not just known public entry points.

## Tool

```text
tools/discover-role-admin-markup-owners.php
```

## Output

The tool writes:

```text
var/reports/role-admin-markup-owners.txt
var/reports/role-admin-markup-owners.log
var/reports/role-admin-markup-owners-source/
```

The report lists every method and flags whether it contains:

- `<form`;
- `<table`;
- `<input`;
- `<label`;
- `<ul` or `<li`;
- heredoc syntax.

## Next phase rule

The next phase should implement the actual controller cutover by moving all flagged markup-owning methods to template rendering or template partials, then run:

```bash
php8.5 vendor/bin/pest
php8.5 tools/audit-role-admin-latte-closeout.php --enforce-closed
```
