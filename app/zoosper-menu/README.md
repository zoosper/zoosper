# zoosper/menu

Site-scoped navigation, nested menu trees, breadcrumbs and menu API for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Menu\` maps to `src/`.

## Architecture

- `src/Admin/`
- `src/Api/`
- `src/Application/`
- `src/Contract/`
- `src/Frontend/`
- `src/Model/`
- `src/Repository/`
- `src/Service/`
- `src/Tree/`

## Configuration

- `config/acl.php`: ACL groups and permissions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/api_routes.php`: API routes.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/menus` from `config/admin_routes.php`.
- `GET /admin/menus/create` from `config/admin_routes.php`.
- `POST /admin/menus` from `config/admin_routes.php`.
- `GET /admin/menus/{id:\\d+}/edit` from `config/admin_routes.php`.
- `POST /admin/menus/{id:\\d+}/edit` from `config/admin_routes.php`.
- `POST /admin/menus/{id:\\d+}/items` from `config/admin_routes.php`.
- `POST /admin/menus/{id:\\d+}/items/{itemId:\\d+}` from `config/admin_routes.php`.
- `POST /admin/menus/{id:\\d+}/items/{itemId:\\d+}/delete` from `config/admin_routes.php`.
- `POST /admin/menus/{id:\\d+}/delete` from `config/admin_routes.php`.
- `GET /api/v1/menu` from `config/api_routes.php`.

## Dependencies

- `php`: `^8.3 || ^8.5`.
- `zoosper/core`: `dev-dev`.
- `zoosper/page`: `dev-dev`.
- `zoosper/site`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202608100001_create_menu_tables.php`, `database/migrations/202608100002_seed_menu_permission.php`.

## Extension points

- `config/acl.php` for ACL declarations.
- `config/admin_menu.php` for Admin navigation.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.
- API routes should remain stateless unless their route contract explicitly states otherwise.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-menu/tests`.
- Current regression files discovered: `16`. Use `find app/zoosper-menu/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Referential integrity
- Menus cascade with their Site. Menu items cascade with their Menu and parent item. Page deletion sets optional `page_id` references to null.
- These declarative relationships mirror the existing migration semantics and are covered by drift tests.
