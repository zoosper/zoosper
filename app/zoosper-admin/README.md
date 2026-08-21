# zoosper/admin

Zoosper_Admin module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Admin\` maps to `src/`.

## Architecture

- `src/Asset/`
- `src/Audit/`
- `src/Controller/`
- `src/Editor/`
- `src/Form/`
- `src/Grid/`
- `src/I18n/`
- `src/Layout/`
- `src/Message/`
- `src/Navigation/`
- `src/Routing/`
- `src/UI/`

## Configuration

- `config/admin_assets.php`: Admin asset contributions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_sections.php`: Admin section labels, icons, and order.
- `config/admin_settings.php`: Settings catalogue contributions.
- `config/assets.php`: Runtime asset registration.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/login` from `config/admin_routes.php`.
- `POST /admin/login` from `config/admin_routes.php`.
- `POST /admin/logout` from `config/admin_routes.php`.
- `GET /admin` from `config/admin_routes.php`.
- `GET /admin/audit-log` from `config/admin_routes.php`.
- `GET /admin/login-history` from `config/admin_routes.php`.

## Dependencies

- `marko/admin`: `0.8.5`.
- `php`: `^8.5`.
- `zoosper/admin-grid`: `dev-dev`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- `zoosper/errors`: `dev-dev`.
- `zoosper/grid`: `dev-dev`.
- `zoosper/theme`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.

## Extension points

- `config/admin_assets.php` for Admin assets.
- `config/admin_menu.php` for Admin navigation.
- `config/admin_sections.php` for Admin section metadata.
- `config/admin_settings.php` for Settings catalogue entries.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-admin/tests`.
- Current regression files discovered: `23`. Use `find app/zoosper-admin/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

## Pagination ownership

This package directly consumes the stable `Zoosper\Pagination` request/result boundary through `zoosper/pagination` (`dev-dev`). It must not import `Marko\Pagination` classes.
