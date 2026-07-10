# Pages, Users and Roles View Refactor

Phase 0.19 introduces module-owned admin views for the heavier admin controllers:

- `PageAdminController`
- `UserAdminController`
- `RoleAdminController`

## Module-owned views

```text
app/zoosper-page/resources/views/admin/pages/index.php
app/zoosper-page/resources/views/admin/pages/form.php
app/zoosper-auth/resources/views/admin/users/index.php
app/zoosper-auth/resources/views/admin/users/form.php
app/zoosper-auth/resources/views/admin/roles/index.php
app/zoosper-auth/resources/views/admin/roles/form.php
```

## Shared admin components

```text
themes/admin/default/templates/components/error.php
themes/admin/default/templates/components/actions.php
themes/admin/default/templates/components/checkbox-list.php
```

## Refactor target

Controllers should gradually move from string-built HTML to:

```php
$this->views->render(
    title: 'Admin Pages',
    template: 'zoosper-page::admin/pages/index',
    data: ['pages' => $pages],
    user: $user,
    active: 'admin-pages',
);
```

This keeps routes/controllers/API/ACL in their own modules, while admin themes can override output.
