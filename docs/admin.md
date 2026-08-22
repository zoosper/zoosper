# Admin extension points

Modules extend Admin through route manifests, permissions, menu declarations, form configuration, grid workspaces, assets, layouts, translations and service composition.

Admin assets are module-owned and registered through `config/admin_assets.php`. Both the wrapped `assets` form and established flat declaration form are supported.

The default Admin theme provides a fluid responsive shell with Admin-owned design tokens. Its external module CSS and JavaScript provide light/dark theme selection, desktop navigation collapse, mobile off-canvas navigation, keyboard focus containment and restoration, visible focus states, and reduced-motion support. The shell uses full-width `minmax(0, 1fr)` content so feature screens—not a fixed global maximum—control their useful working width.

Shell behaviour is progressive: server-rendered navigation and content remain available if JavaScript is unavailable. Production templates must not add inline JavaScript, event handlers, styles or dynamic `innerHTML`. Feature modules should contribute navigation, assets and rendered content through existing Admin contracts rather than depend on shell selectors or implementation classes.

## Shared component presentation

The Admin-owned `admin-components.css` layer provides theme-aware page headers, responsive card grids, cards, controls, forms, toolbars, actions, notices, badges, horizontally scrollable data tables, pagination and empty states. It loads after shell tokens and before feature assets. Default-theme components preserve semantic headings, alert announcements, column scopes and labelled keyboard-scrollable table regions. Responsive controls remain usable at narrow widths and component motion is disabled for `prefers-reduced-motion`.

Feature modules should reuse these presentation contracts for ordinary Admin UI while retaining ownership of domain data and behaviour. Specialised packages such as `zoosper/admin-grid` continue to own their markup, scripts and detailed styles; the shared layer does not create a concrete reverse dependency or move Grid behaviour into the Admin module.

Routes must declare permissions. Stateful Admin mutations are protected by authentication and CSRF middleware. Admin controllers should delegate rendering and domain work to collaborators. Shell redesigns do not alter route methods, permissions, ownership filters or CSRF boundaries.

## Page revisions

The Page edit screen lists retained revisions. Administrators with `page.manage` may preview a historical snapshot or restore it through a CSRF-protected POST action. Restore captures the current Page as a safety revision before applying the selected title, slug, content, publication state, structured content and SEO metadata.
