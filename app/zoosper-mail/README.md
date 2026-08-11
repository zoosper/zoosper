# zoosper/mail

Zoosper_Mail module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Mail\` maps to `src/`.

## Architecture

- `src/Config/`
- `src/Controller/`
- `src/Diagnostics/`
- `src/Log/`
- `src/Message/`
- `src/Transport/`

## Configuration

- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_settings.php`: Settings catalogue contributions.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/mail-logs` from `config/admin_routes.php`.
- `GET /admin/mail-logs/view` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202607310001_expand_smtp_email_log_bodies.php`.

## Extension points

- `config/admin_settings.php` for Settings catalogue entries.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-mail/tests`.
- Current regression files discovered: `8`. Use `find app/zoosper-mail/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
