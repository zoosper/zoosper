# zoosper/settings

Module-owned settings catalogue and configuration management for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Settings\` maps to `src/`.

## Architecture

- `src/Admin/`
- `src/Audit/`
- `src/Catalogue/`
- `src/Controller/`
- `src/Definition/`
- `src/Persistence/`
- `src/Scope/`
- `src/Value/`
- `src/Write/`

## Configuration

- `config/admin_assets.php`: Admin asset contributions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_settings.php`: Settings catalogue contributions.
- `config/assets.php`: Runtime asset registration.
- `config/controllers.php`: Controller factories.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/settings` from `config/admin_routes.php`.
- `POST /admin/settings/save` from `config/admin_routes.php`.
- `POST /admin/settings/clear` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- `zoosper/site`: `dev-dev`.

## Extension points

- `config/admin_assets.php` for Admin assets.
  Settings workspace CSS and JavaScript declare `screens => ['settings']` and initialise only when their complete DOM roots exist. The module-owned CSS consumes Admin semantic surface, border, text, focus, and shadow tokens for coherent light/dark presentation. On desktop, More Actions uses a bounded, scroll-safe three-group panel with a wider two-column Share and Output group. At `850px` and below it retains the static single-column flow, with an explicit `390px` layout contract.
- `config/admin_menu.php` for Admin navigation.
- `config/admin_settings.php` for Settings catalogue entries.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-settings/tests`.
- Current regression files discovered: `78`. Use `find app/zoosper-settings/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.


### Fable workspace presentation

The Settings-owned stylesheet now presents the catalogue as a calm two-pane workspace with a stronger scope bar, category rail, editing hierarchy, and sticky save surface. It consumes Admin semantic tokens and retains the existing module discovery, scope provenance, saved-view runtime, POST/CSRF mutations, print output, and narrow-screen fallback.
