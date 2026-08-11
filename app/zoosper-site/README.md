# zoosper/site

Zoosper_Site module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Site\` maps to `src/`.

## Architecture

- `src/Admin/`
- `src/Console/`
- `src/Infrastructure/`
- `src/Model/`
- `src/Repository/`
- `src/Site/`

## Configuration

- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/console.php`: Console command discovery.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/sites` from `config/admin_routes.php`.
- `GET /admin/sites/create` from `config/admin_routes.php`.
- `POST /admin/sites/create` from `config/admin_routes.php`.
- `GET /admin/sites/edit` from `config/admin_routes.php`.
- `POST /admin/sites/edit` from `config/admin_routes.php`.
- `GET /admin/site-domains` from `config/admin_routes.php`.
- `GET /admin/site-domains/create` from `config/admin_routes.php`.
- `POST /admin/site-domains/create` from `config/admin_routes.php`.
- `GET /admin/site-domains/edit` from `config/admin_routes.php`.
- `POST /admin/site-domains/edit` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202607090003_create_site_tables.php`, `database/migrations/202607090008_site_theme_code.php`.

## Extension points

- `config/admin_menu.php` for Admin navigation.
- `config/console.php` for console commands.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-site/tests`.
- Current regression files discovered: `4`. Use `find app/zoosper-site/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
