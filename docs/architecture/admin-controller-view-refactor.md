# Admin Controller View Refactor

Phase 0.18 starts moving admin controllers away from inline HTML strings.

## Refactored controllers

- `DashboardController`
- `AuditLogController`
- `LoginHistoryController`
- `ThemeAdminController`

## Pattern

Controllers now prepare data and call `AdminViewRenderer` where available:

```php
$this->views->render(
    title: 'Audit Log',
    template: 'zoosper-admin::audit-log/index',
    data: ['rows' => $rows],
    user: $user,
    active: 'audit-log',
);
```

## Module-owned views

```text
app/zoosper-admin/resources/views/audit-log/index.php
app/zoosper-admin/resources/views/login-history/index.php
app/zoosper-theme/resources/views/admin/themes/index.php
```

## Theme overrides

```text
themes/admin/default/templates/modules/zoosper-admin/audit-log/index.php
themes/admin/default/templates/modules/zoosper-admin/login-history/index.php
themes/admin/default/templates/modules/zoosper-theme/admin/themes/index.php
```

## Next refactors

- pages admin
- users admin
- roles admin
- login page template
