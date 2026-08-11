# zoosper/store-orders

Store Orders API Grid integration for Zoosper.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\StoreOrders\` maps to `src/`.

## Architecture

- `src/Admin/`
- `src/Api/`
- `src/StoreOrderCapabilities.php`
- `src/StoreOrderDataSourceFactory.php`
- `src/StoreOrderGrid.php`

## Configuration

- `config/acl.php`: ACL groups and permissions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/controllers.php`: Controller factories.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/store-orders` from `config/admin_routes.php`.
- `GET /admin/store-orders/export` from `config/admin_routes.php`.
- `POST /admin/store-orders` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/admin-grid`: `dev-dev`.
- `zoosper/api-grid`: `dev-dev`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- `zoosper/grid`: `dev-dev`.
- Development dependencies: `pestphp/pest`, `pestphp/pest-plugin`, `phpunit/phpunit`.

## Database

- Module migrations: `database/migrations/202608020001_seed_store_order_permissions.php`.

## Extension points

- `config/acl.php` for ACL declarations.
- `config/admin_menu.php` for Admin navigation.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-store-orders/tests`.
- Current regression files discovered: `16`. Use `find packages/zoosper-store-orders/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
