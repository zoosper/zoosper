# zoosper/auth

Zoosper_Auth module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Auth\` maps to `src/`.

## Architecture

- `src/Access/`
- `src/Acl/`
- `src/Admin/`
- `src/Console/`
- `src/Entity/`
- `src/Http/`
- `src/Layout/`
- `src/Model/`
- `src/RateLimit/`
- `src/Repository/`
- `src/Security/`
- `src/Service/`
- `src/UI/`

## Configuration

- `config/acl.php`: ACL groups and permissions.
- `config/admin_assets.php`: Admin asset contributions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_middleware.php`: Module runtime configuration.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_ui.php`: Module runtime configuration.
- `config/console.php`: Console command discovery.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.
- `config/services_auth_grid.php`: Module runtime configuration.

## Routes

- `GET /admin/users` from `config/admin_routes.php`.
- `GET /admin/users/create` from `config/admin_routes.php`.
- `POST /admin/users/create` from `config/admin_routes.php`.
- `GET /admin/users/edit` from `config/admin_routes.php`.
- `POST /admin/users/edit` from `config/admin_routes.php`.
- `GET /admin/roles` from `config/admin_routes.php`.
- `GET /admin/roles/create` from `config/admin_routes.php`.
- `POST /admin/roles/create` from `config/admin_routes.php`.
- `GET /admin/roles/edit` from `config/admin_routes.php`.
- `POST /admin/roles/edit` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/admin-grid`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- `zoosper/grid`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202607090001_create_auth_tables.php`, `database/migrations/202607090002_seed_auth_defaults.php`, `database/migrations/202607090005_seed_user_role_permissions.php`, `database/migrations/202607090007_acl_tree_metadata.php`.

## Extension points

- `config/acl.php` for ACL declarations.
- `config/admin_assets.php` for Admin assets.
- `config/admin_menu.php` for Admin navigation.
- `config/console.php` for console commands.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-auth/tests`.
- Current regression files discovered: `35`. Use `find app/zoosper-auth/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Referential integrity
- Admin user-role and role-permission join rows cascade when their owning User, Role, or Permission is removed.
- Declarative schema now mirrors the migration-owned join-table relationships without changing public repository contracts.
