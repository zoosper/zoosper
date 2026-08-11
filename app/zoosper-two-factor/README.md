# zoosper/two-factor

Zoosper_TwoFactor module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\TwoFactor\` maps to `src/`.

## Architecture

- `src/Challenge/`
- `src/Controller/`
- `src/Crypto/`
- `src/Qr/`
- `src/Recovery/`
- `src/Repository/`
- `src/Service/`
- `src/Totp/`
- `src/Value/`

## Configuration

- `config/admin_assets.php`: Admin asset contributions.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/grid_columns.php`: Grid column contributions.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/2fa/setup` from `config/admin_routes.php`.
- `POST /admin/2fa/setup` from `config/admin_routes.php`.
- `GET /admin/2fa/challenge` from `config/admin_routes.php`.
- `POST /admin/2fa/challenge` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.

## Extension points

- `config/admin_assets.php` for Admin assets.
- `config/grid_columns.php` for Grid columns.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-two-factor/tests`.
- Current regression files discovered: `7`. Use `find app/zoosper-two-factor/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
