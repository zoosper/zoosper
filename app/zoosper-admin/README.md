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
- `src/Dashboard/`
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
- `config/admin_dashboard.php`: permission-gated Dashboard widget contributors.
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
- `POST /admin/dashboard/preferences` saves the current user's permitted widget visibility and order.
- `POST /admin/dashboard/preferences/reset` resets the current user to assigned-role defaults, or module defaults when no assigned role is configured.
- `GET|POST /admin/dashboard/role-defaults` and `POST /admin/dashboard/role-defaults/reset` provide `role.manage`-protected role default administration.
- `GET /admin/audit-log` from `config/admin_routes.php`.
- `GET /admin/login-history` from `config/admin_routes.php`.

## Dependencies

- `marko/admin`: `0.8.5`.
- `php`: `^8.5`.
- `zoosper/admin-grid`: `^0.3.1@alpha`.
- `zoosper/auth`: `^0.3.1@alpha`.
- `zoosper/core`: `^0.3.1@alpha`.
- `zoosper/errors`: `^0.3.1@alpha`.
- `zoosper/grid`: `^0.3.1@alpha`.
- `zoosper/theme`: `^0.3.1@alpha`.

## Database

- Declarative schema is owned by `config/db_schema.php`, including per-user `admin_dashboard_preferences`.

## Extension points

- `config/admin_assets.php` for Admin assets.
- `config/admin_colour_themes.php` for trusted Admin colour-palette metadata.
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

The palette preference uses `zoosper.admin.theme` in browser local storage and falls back to `prefers-color-scheme`. Enabled modules contribute declarative `config/admin_colour_themes.php` manifests containing stable palette code, display name, `light` or `dark` base mode, and integer sort order. Invalid manifests, duplicate codes, and missing compatibility palettes fail closed. Palette CSS remains an external, CSP-safe module asset registered separately through `config/admin_assets.php`; manifests cannot inject CSS or behaviour. The root `data-admin-theme-palette` attribute identifies the selected palette, while `data-admin-theme` remains its `light` or `dark` base mode so existing feature selectors remain compatible. Admin supplies Light, Dark, and the dark-derived Ocean palette. The same root theme state drives the content, top bar and complete left navigation. Navigation hover, active, divider, scrollbar, border and brand colours are semantic tokens rather than fixed dark-only values. The desktop collapse preference uses `zoosper.admin.sidebar-collapsed`. Storage failure is non-fatal and does not disable the controls. The top-bar leading region is explicitly flexible and clipped, so its title truncates instead of colliding with shell controls when navigation is collapsed or the viewport narrows. Collapsed links retain escaped labels as native hover/focus titles, group boundaries remain visible, the active destination has a non-colour-only inset marker, and logout keeps its existing POST/CSRF boundary while using the shared navigation-icon structure. The collapse control is rendered at the bottom of its owning sidebar rather than in the top bar, preserving premium page-title space. The single Admin shell runtime updates the palette selector, collapse-control label, arrow, and persisted state; no second behaviour owner is introduced.

Each feature module owns its destination's semantic `icon` identifier in `config/admin_menu.php`; section metadata remains in `config/admin_sections.php`. `AdminNavigationRenderer` maps supported destination identifiers to restrained, current-colour SVG line icons and emits a neutral safe fallback for an empty or unknown identifier. Section headings are semantic, text-only, non-interactive headings when expanded and become spacing/divider boundaries when collapsed, so they cannot be mistaken for destinations. Identifier values remain escaped metadata and never become executable or caller-supplied SVG markup. This keeps collapsed navigation complete without icon fonts, external requests, inline scripts, or concrete feature dependencies in the Admin shell.

## Dashboard

The Dashboard discovers enabled modules' `config/admin_dashboard.php` declarations through `ModuleDashboardWidgetLoader`. Every contributor declaration requires a permission, which is checked before its service is resolved or executed. Contributors implement the dependency-safe `zoosper/admin-dashboard` contract and return immutable plain-text widget values; feature modules own their queries while Admin owns deterministic composition, duplicate rejection, failure isolation, responsive rendering, escaping, and the generic unavailable state. The Auth module supplies the first live metric: active Admin users, visible only with `user.manage`.

Admin owns per-user Dashboard preferences in `admin_dashboard_preferences`; Auth owns role defaults and role-assignment lookup through the `zoosper/admin-dashboard` repository contract. Resolution is permission filtering, explicit per-user preference, configured assigned-role defaults, then module defaults. Multiple configured roles merge as a visible-widget union with deterministic role-code order; stale codes are ignored and newly permitted widgets append in module order. Resetting a user layout therefore returns to role defaults when present. Role managers can edit only widgets available to their own account, while inaccessible stored codes are preserved and never disclosed; defaults never grant widget access. Standard POST forms remain the server-authoritative fallback, central Admin CSRF middleware validates mutations, role-default changes are audited, and the screen-scoped `dashboard-personalisation.js` asset adds drag, keyboard move, and visibility feedback without inline behaviour.

## Shared Admin components

`resources/assets/css/admin-components.css` is the Admin-owned presentation contract for reusable page hierarchy, cards, forms, buttons, toolbars, notices, badges, tables, pagination and empty states. It consumes shell design tokens, supports light and dark themes, remains fluid at narrow widths and removes component transitions when reduced motion is requested. It is registered immediately after the shell stylesheet and before feature styles so an owning feature package can refine its own specialised UI without copying the shared foundation.

The Fable-informed foundation is reconciled into this existing semantic owner rather than added as a competing stylesheet. Its light palette, strong/body/muted/faint text hierarchy, spacing scale (`4`, `8`, `12`, `16`, `20`, `24`, `32`, `40` pixels), radius scale (`6`, `10`, `14` pixels plus pill), elevation tiers and darker primary action are exposed through `--admin-*` tokens. Dark and Ocean retain independent surface, text, focus and elevation values. The shell remains fluid and navigation remains free of decorative numeric count badges.

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

This package directly consumes the stable `Zoosper\Pagination` request/result boundary through `zoosper/pagination` (`^0.3.1@alpha`). It must not import `Marko\Pagination` classes.


### Fable bulk presentation pass

The compact shell, native account disclosure, Dashboard hierarchy and responsive customisation surface consume the shared semantic foundation. Logout remains a CSRF-bearing POST form, the existing shell runtime remains the only theme/navigation behaviour owner, and only real permission-filtered module widgets are rendered. No navigation count badges, fabricated search, notifications, routes or records are introduced.


### Role workspace presentation

Admin-owned role templates use shared cards, responsive permission and assignment grids, semantic table regions, and a sticky action surface. Auth remains the behaviour and authorization owner; role writes remain POST-only and CSRF protected.

## Public Admin password-reset HTTP lifecycle
The Admin module owns the thin public HTTP adapter and declares four stateful routes:

- `GET /admin/forgot-password` renders the CSRF-bearing request form.
- `POST /admin/forgot-password` applies CSRF middleware, the dedicated reset-request limiter, neutral account-discovery output, Auth issuance, and delivery through the Auth-owned abstraction.
- `GET /admin/reset-password` renders the escaped credential in a hidden form value.
- `POST /admin/reset-password` applies CSRF middleware and returns HTTP `422` for rejected reset attempts or HTTP `303` to `/admin/login?reset=complete` after success.

Both public pages publish `noindex,nofollow`. Missing or expired CSRF state is rejected with HTTP `419`. Unknown, inactive, rate-limited, and delivery-failure request cases share the same public response. Successful reset rotates the CSRF token and records only the secret-free `admin.password_reset_completed` audit action. The reset controller does not authenticate the user automatically.

`PasswordResetHttpAcceptanceTest` boots the real application service graph and exercises the Router, authentication middleware, CSRF middleware, migrations, rate limiter, reset service, password authentication, credential replay prevention, and session fingerprint invalidation.

## Admin account-lockout operations

The Admin login controller deliberately presents the same `Invalid email or password.` response for an incorrect password and a temporarily locked account. Operators must not add lockout status, expiry, attempt counts, or account-existence details to the public form.

Authorised operators can inspect account-lockout state on the protected Admin User edit screen. When failure state exists, the workspace shows the failed-attempt count. An active lock also shows its expiry in UTC and an **Unlock account** action; pre-threshold state offers **Clear failed attempts**.

The mutation is `POST /admin/users/{id}/unlock`, requires `user.manage`, uses the standard stateful Admin CSRF middleware, and responds with HTTP 303 to the target edit screen. It changes only Auth-owned lockout state and writes the secret-free `admin_user.account_unlocked` audit action when state existed.
