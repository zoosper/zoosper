# zoosper/admin-dashboard

Dependency-safe contracts and immutable values for module-contributed Zoosper Admin Dashboard widgets.

## Responsibilities

This library owns `DashboardWidgetContributorInterface`, the escaped-data-only `DashboardWidget` value, immutable Dashboard role values, and `DashboardRolePreferenceRepositoryInterface`. It contains no controller, template, module discovery, service container, authentication, persistence, or feature-module dependency. `zoosper/admin` owns discovery, permission filtering, composition, and rendering. The role-owning module implements persistence and assignment lookup through the contract; feature modules own contributor services and data reads.

## Configuration and extension points

An enabled module registers its contributor as a normal service and declares it in `config/admin_dashboard.php`:

```php
return [[
    'service' => MyDashboardContributor::class,
    'permission' => 'example.view',
]];
```

Every declaration requires a non-empty permission. Admin checks permission before resolving the service, preventing unauthorised contributors from executing. Contributors return immutable widgets containing plain text only; they must not return HTML, scripts, credentials, secrets, or mutation controls.

Role defaults are supplied through `DashboardRolePreferenceRepositoryInterface`. Admin permission-filters the actual user first, applies an explicit per-user layout when present, otherwise merges configured assigned roles as a visible union in deterministic role-code order, and finally falls back to module order. Implementations must preserve tenant/user isolation and return only roles assigned to the requested Admin user.

## Dependencies

Runtime dependency: PHP `^8.5` only. Feature modules may depend on this contracts package without depending on the concrete `zoosper/admin` module.

## Testing

Run `zcomposer test` inside the package checkout, or run `php8.5 vendor/bin/pest packages/zoosper-admin-dashboard/tests` from the repository root.

- Standard repository quality gate: `php8.5 tools/gate.php`.

## Operational notes

Keep contributor reads bounded, tenant/site-aware where applicable, side-effect free, and safe to retry. Exceptions are isolated by Admin and produce only a generic availability notice; contributors must still log operational failures through their owning module's approved logging boundary. Dashboard rendering escapes every field.
