# zoosper/media

Zoosper Media module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Media\` maps to `src/`.

## Architecture

- `src/Controller/`
- `src/EditorJs/`
- `src/Model/`
- `src/Processing/`
- `src/Repository/`
- `src/Service/`

## Configuration

- `config/acl.php`: ACL groups and permissions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/media` from `config/admin_routes.php`.
- `GET /admin/media/upload` from `config/admin_routes.php`.
- `POST /admin/media/upload` from `config/admin_routes.php`.
- `POST /admin/media/editorjs/upload` from `config/admin_routes.php`.

## Dependencies

- `ext-pdo`: `*`.
- `php`: `^8.5`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- Development dependencies: `pestphp/pest`, `pestphp/pest-plugin`, `phpunit/phpunit`.

## Database

- Declarative schema is owned by `config/db_schema.php`.

## Extension points

- `config/acl.php` for ACL declarations.
- `config/admin_menu.php` for Admin navigation.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-media/tests`.
- Current regression files discovered: `26`. Use `find packages/zoosper-media/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Phase 9IF Media lifecycle truth closure
Media assets now use POST-only, media.manage, CSRF-protected archive, restore and archived-first permanent deletion boundaries. Metadata deletion is transactional and owned-file cleanup is conservative and audited. Upload derivatives remain disabled by default; LocalCopyMediaProcessor is not image transformation support.
