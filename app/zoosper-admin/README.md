# zoosper-admin

Zoosper's server-rendered administration module. It owns the Admin shell, navigation presentation, dashboard, audit and login-history screens, module-owned Admin assets, flash messages, and bindings for shared Admin rendering contracts.

## Architecture

- `src/Layout/AdminLayout.php` composes the secured Admin shell.
- `src/Navigation/` discovers module menu declarations, filters them by ACL, builds Marko-compatible sections, and renders escaped navigation markup.
- `src/UI/AdminViewRenderer.php` renders module-owned Admin content inside the shared layout.
- `config/services.php` wires runtime services and shared Auth/Core interfaces.
- `config/admin_menu.php` contributes this module's menu items.
- `config/admin_sections.php` declares section labels, icons, and ordering.

## Extension points

Modules may contribute `config/admin_menu.php`, `config/admin_sections.php`, Admin assets, controllers, views, grids, permissions, and form sections without editing `zoosper-admin` source. Menu links are transformed through the configured Admin base path and filtered before section rendering.

## Security

Admin state-changing routes remain protected by authentication, ACL, central CSRF middleware, optional two-factor authentication, and POST-only logout. Navigation labels, URLs, section metadata, and icon identifiers are escaped before output.

## Testing

Run the complete project suite with `zcomposer test`. Run the Admin navigation suite with `php8.5 vendor/bin/pest app/zoosper-admin/tests/Unit/Navigation app/zoosper-core/tests/Unit/Admin`. Run the standard quality gate with `php8.5 tools/gate.php`.

## Admin navigation sections

Modules contribute links through `config/admin_menu.php`. Section presentation metadata is independently mergeable through `config/admin_sections.php` using `id`, `label`, optional `icon`, and `sort_order`. Section IDs are normalised to lowercase kebab-case. Later module declarations replace earlier metadata for the same ID, while links remain permission-filtered by `AdminMenu` before section construction.
