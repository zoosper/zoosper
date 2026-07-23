# RoleAdminController Markup View Cutover

This document records the Phase 1.38 source-specific cutover from controller-owned role admin markup into view partials.

## Source evidence

The markup ownership map identified exactly four markup-owning methods in `RoleAdminController`:

```text
index
form
permissionTree
userAssignment
```

The remaining methods were reported as not owning markup.

## Strategy

This phase keeps existing controller behaviour and the `AdminLayout::render()` seam, while moving HTML generation into role admin view partials under:

```text
app/zoosper-admin/resources/views/admin/roles/
```

The controller should keep responsibility for:

- authentication/user guard checks;
- repository calls;
- CSRF token creation;
- selected permission/user id calculation;
- redirects;
- audit logging;
- selecting the correct view partial.

The views should own HTML markup for:

- role index table;
- role form;
- permission tree checkboxes;
- user assignment checkboxes.

## Tool

```text
tools/apply-role-admin-markup-view-cutover.php
```

Default mode is read-only:

```bash
php8.5 tools/apply-role-admin-markup-view-cutover.php
```

Apply mode is explicit:

```bash
php8.5 tools/apply-role-admin-markup-view-cutover.php --apply
```

## Closeout

After apply, run:

```bash
php8.5 vendor/bin/pest
php8.5 tools/audit-role-admin-latte-closeout.php --enforce-closed
```

If both pass, Phase 1.38 can be closed.
