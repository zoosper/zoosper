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

## Responsive Admin shell

The default Admin theme owns the semantic shell markup in `themes/admin/default/templates/layout.php`. The Admin module owns its progressive presentation and behaviour through:

- `resources/assets/css/admin-shell.css`: fluid design tokens, light and dark colour schemes, full-width content, desktop sidebar collapse, mobile off-canvas navigation, focus-visible treatment and reduced-motion handling.
- `resources/assets/js/admin-shell.js`: stored theme/sidebar preferences plus accessible navigation state, Escape handling, focus restoration and mobile focus containment.

Both files are registered by `config/admin_assets.php` and served through the module asset route. Shell templates must not add inline scripts, event handlers or styles. Feature modules contribute escaped navigation and page content through existing contracts; they must not couple to the shell implementation. Existing Admin screen styles remain supported through compatibility token mappings while screens migrate gradually.

Admin asset declarations may include `screens`, a list of generic active-screen codes supplied to `AdminLayout::render()`. Missing or empty `screens` keeps an asset global. Filtering occurs before physical-path de-duplication, and no-argument registry APIs continue to return the complete diagnostic inventory. EditorJS assets are restricted to the proven `pages` screen instead of loading on unrelated Admin routes.

The theme preference uses `zoosper.admin.theme` in browser local storage and falls back to `prefers-color-scheme`. The same root theme state drives the content, top bar and complete left navigation: light mode uses a light sidebar palette, while dark mode retains the high-contrast dark palette. Navigation hover, active, divider, scrollbar, border and brand colours are semantic tokens rather than fixed dark-only values. The desktop collapse preference uses `zoosper.admin.sidebar-collapsed`. Storage failure is non-fatal and does not disable the controls.

## Shared Admin components

`resources/assets/css/admin-components.css` is the Admin-owned presentation contract for reusable page hierarchy, cards, forms, buttons, toolbars, notices, badges, tables, pagination and empty states. It consumes shell design tokens, supports light and dark themes, remains fluid at narrow widths and removes component transitions when reduced motion is requested. It is registered immediately after the shell stylesheet and before feature styles so an owning feature package can refine its own specialised UI without copying the shared foundation.

The default theme's reusable component templates add semantic card regions, alert announcements, scoped table headings, labelled keyboard-scrollable table regions and live pagination summaries. Templates remain server-rendered and contain no inline style or behaviour. Feature modules may use these semantic classes, but must continue to own domain-specific rendering, routes, permissions and mutations. `zoosper/admin-grid` continues to own Grid workspace structure and behaviour; shared Admin CSS must not replace its package assets.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.
- Shell assets are CSP-compatible external module assets and use event listeners and safe text updates; do not introduce `innerHTML` or inline executable behaviour.
- Shell changes must preserve the canonical shared logo and favicon references, logout's POST/CSRF boundary, escaped navigation output and module asset ordering.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-admin/tests`.
- Current regression files discovered: `25`. Use `find app/zoosper-admin/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

## Pagination ownership

This package directly consumes the stable `Zoosper\Pagination` request/result boundary through `zoosper/pagination` (`dev-dev`). It must not import `Marko\Pagination` classes.
