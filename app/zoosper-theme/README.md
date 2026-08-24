# zoosper/theme

Theme and template rendering foundation for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Theme\` maps to `src/`.

## Architecture

- `src/Config/`
- `src/Layout/`
- `src/Template/`
- `src/Theme/`

## Configuration

- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_settings.php`: Settings catalogue contributions.
- `config/controllers.php`: Controller factories.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.
- `config/theme.php`: Module runtime configuration.

## Routes

- `GET /admin/themes` from `config/admin_routes.php`.
- `POST /admin/themes/assign` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.
- `zoosper/errors`: `dev-dev`.

## Extension points

- `config/admin_menu.php` for Admin navigation.
- `config/admin_settings.php` for Settings catalogue entries.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-theme/tests`.
- Current regression files discovered: `12`. Use `find app/zoosper-theme/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

#### SEO head contract
- Frontend theme layouts consume escaped SEO values supplied by the Page module. PHP and Latte layouts provide equivalent title, description, robots, canonical and Open Graph basics.
- Themes must escape metadata and must not recompute canonical URLs.

## Feature-owned API
The filesystem Theme catalogue exposes safe code, name, and version metadata only. Site assignment uses a Theme-owned application service and never exposes Theme filesystem paths.

### Admin ownership
The Theme Admin adapter is owned by this module and reuses ThemeAssignmentService and AuditLoggerInterface.

## Admin theme workspace

The Theme-owned Admin view uses shared semantic Admin components for the installed-theme catalogue and per-site assignment cards. Both the module template and default Admin-theme override consume the controller-supplied escaped assignment URL, retain POST and CSRF fields, and provide equivalent empty states. Theme assignment remains owned by `ThemeAssignmentService`; this presentation does not define Admin shell colour-theme registration.
