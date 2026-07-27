# Grid Extensibility

## Problem

Every admin grid's columns/filters were hard-coded in one class
(`AuditLogGrid`, `LoginHistoryGrid`, ...), so no OTHER module could add a
column relevant to its own data without editing that class directly - exactly
the coupling Magento's UI Component DI-merge and WordPress's
`manage_{screen}_columns` hook both exist to prevent.

## Mechanism

Any module may ship `config/grid_columns.php`:

```php
return [
    '<grid-key>' => [
        'columns' => [ new GridColumn(...), ... ],
        'filters' => [ new GridFilter(...), ... ],
    ],
];
```

`GridColumnRegistry::apply($gridKey, $baseDefinition)` discovers every enabled
module's contribution (via `ModuleRegistry`, no dependency on config-merge
internals), and returns a NEW `GridDefinition` with the contributed
columns/filters appended. An extending module cannot override a key the grid
already declares - the base definition's column/filter always wins.

## Proof

`zoosper-two-factor` contributes a "User Agent" column to the `login-history`
grid (owned by `zoosper-admin`) - real, previously-uncaptured-in-the-UI data,
added with zero changes to the owning module.

## Next step

Per-user column/filter visibility persistence ("bookmarks") - needs a new DB
table; deferred pending `app/zoosper-admin/config/db_schema.php` so the new
table can be added without risking existing declarations.
