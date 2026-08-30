# Admin extension points

Modules extend Admin through route manifests, permissions, menu declarations, form configuration, grid workspaces, assets, layouts, translations and service composition.

Admin assets are module-owned and registered through `config/admin_assets.php`. Both the wrapped `assets` form and established flat declaration form are supported.

Feature assets can declare a `screens` list using the generic active-screen code passed to the Admin layout. Declarations without a screen list remain global. Applicability is evaluated before physical-path de-duplication, while no-argument registry APIs preserve the complete diagnostic inventory. Settings assets load only for `settings`; Admin-owned EditorJS assets load only for the proven `pages` screen. Feature runtimes must still return safely when their required DOM contract is absent.

The default Admin theme provides a fluid responsive shell with Admin-owned design tokens. Its external module CSS and JavaScript provide light/dark theme selection across both the content area and complete left navigation, desktop navigation collapse, mobile off-canvas navigation, keyboard focus containment and restoration, visible focus states, and reduced-motion support. The sidebar uses theme-owned surface, text, border, hover, active, divider, scrollbar and brand tokens instead of remaining permanently dark. The shell uses full-width `minmax(0, 1fr)` content so feature screens—not a fixed global maximum—control their useful working width. The top-bar title lives in a clipped flexible region and truncates before it can collide with appearance controls. The desktop collapse control sits at the bottom of its owning sidebar so it does not consume page-title space; the existing Admin runtime remains its single behaviour and persistence owner.

Shell behaviour is progressive: server-rendered navigation and content remain available if JavaScript is unavailable. Feature modules own semantic destination identifiers in `config/admin_menu.php`; the Admin renderer maps supported identifiers to a fixed, current-colour SVG allow-list and supplies a neutral fallback without accepting caller-provided SVG. Expanded menu groups are semantic text-only non-interactive headings; collapsed groups become spacing and divider boundaries, while destination links retain icons, escaped labels, active markers and focus treatment. Production templates must not add inline JavaScript, event handlers, styles or dynamic `innerHTML`. Feature modules should contribute navigation, assets and rendered content through existing Admin contracts rather than depend on shell selectors or implementation classes.

## Dashboard composition

The Admin-owned Dashboard builds quick-access cards from the module-discovered, permission-filtered `AdminMenu` result. It does not import feature modules, hardcode their routes, or fabricate operational statistics. This keeps new module menu contributions automatically visible to authorised users while preserving the existing route and ACL boundary.

A planned follow-up will replace navigation repetition with useful dynamic widgets contributed through an Admin-owned contract. Feature modules will own widget data and presentation contributions; Admin will own permission-filtered discovery, composition, responsive layout, empty/error containment, and user layout preferences without importing concrete feature implementations. Widget reads must remain bounded, site-aware, observable, cache-safe, progressively rendered, and isolated so one failed contributor cannot break the Dashboard. Contributions must preserve route and ACL checks, tenant/site isolation, output escaping, CSP-safe registered assets, and reduced-motion/accessibility behaviour. This work remains separate from Phase 10AR production-security verification.

## Shared component presentation

The Admin-owned `admin-components.css` layer provides theme-aware page headers, responsive card grids, cards, controls, forms, toolbars, actions, notices, badges, horizontally scrollable data tables, pagination and empty states. It loads after shell tokens and before feature assets. Default-theme components preserve semantic headings, alert announcements, column scopes and labelled keyboard-scrollable table regions. Responsive controls remain usable at narrow widths and component motion is disabled for `prefers-reduced-motion`.

The shared semantic foundation incorporates the approved Fable design language through the existing `--admin-*` owner: an eight-step spacing scale, three structural radii plus pill, four elevation tiers, stronger text hierarchy and theme-specific Light, Dark and Ocean surfaces. It remains fully fluid; fixed prototype content caps and decorative sidebar count badges are intentionally excluded. Feature packages consume these tokens but do not redefine the global design system.

Feature modules should reuse these presentation contracts for ordinary Admin UI while retaining ownership of domain data and behaviour. Specialised packages such as `zoosper/admin-grid` continue to own their markup, scripts and detailed styles; the shared layer does not create a concrete reverse dependency or move Grid behaviour into the Admin module.

Routes must declare permissions. Stateful Admin mutations are protected by authentication and CSRF middleware. Admin controllers should delegate rendering and domain work to collaborators. Shell redesigns do not alter route methods, permissions, ownership filters or CSRF boundaries.

## Page revisions

The Page edit screen lists retained revisions. Administrators with `page.manage` may preview a historical snapshot or restore it through a CSRF-protected POST action. Restore captures the current Page as a safety revision before applying the selected title, slug, content, publication state, structured content and SEO metadata.

## Admin Grid presentation

Admin Grid is a specialised package-owned surface. Its final visual integration layer is registered by `packages/zoosper-admin-grid/config/admin_assets.php` after the established Grid assets. It uses the Admin shell semantic tokens for fluid light and dark themes without moving Grid styling into the generic Admin module.

Compact Grid controls align primary actions, view controls, filters, columns, export, saved state, page size, pagination and direct-page navigation in one package-owned responsive workflow. Filters and Columns have explicit relationships, labelled dismiss controls, mutually exclusive disclosure behaviour, visible keyboard focus, opaque theme-aware surfaces and calm inset spacing. At `390px` (`24.375rem`), controls and panels stack, active-filter chips remain compact wrapping pills, and wide tables retain bounded touch and keyboard-compatible horizontal scrolling. Package-owned odd/even row styling, permanent row separators, restrained vertical cell boundaries and a stronger header boundary apply to both complete Grid workspaces and standalone Grid tables; hover and keyboard focus override the stripe, while selected rows retain the strongest fill plus a leading-edge marker and their checked selection control. The hierarchy is theme-aware and does not make colour the only selection signal.

Grid mutations remain POST-only and retain existing permission, CSRF, application-local path, ownership, persistence, export, and audit boundaries. New Admin screens should compose the shared Admin shell with Admin Grid rather than copying Grid markup or styles into feature modules.

## Personal Access Tokens

The Auth module owns the Personal Access Tokens Admin experience, including its Latte markup and registered CSS/JavaScript. The screen composes the shared responsive Admin shell with the specialised Admin Grid, presents canonical service scopes in accessible domain groups, identifies destructive delete scopes, and provides a CSP-safe one-time copy enhancement without placing secrets in redirects, flash messages, logs or persistent client state.

PAT creation and revocation remain authenticated, centrally CSRF-protected POST operations. Listings and revocation stay scoped to the current Admin identity, and repository, scope-validation, audit and one-time plaintext-disclosure contracts remain Auth-owned. JavaScript only enhances selection feedback and clipboard handling; the server-rendered form remains authoritative and usable without it. Prototype-only statistics, exports, site restrictions, presets and simulated persistence are not production capabilities.

### Personal Access Token presentation refinement

The Auth-owned Personal Access Tokens workspace uses a four/three/two/one-column responsive scope layout and PAT-specific Grid cell renderers for truncated names, scope chips, dates, status badges and compact revoke actions. These renderers escape all persisted values and retain the owner-scoped query, POST-only revoke form and central CSRF contract. Presentation overrides remain scoped beneath the PAT workspace and do not alter generic Admin Grid behaviour.


### Fable bulk presentation pass

The compact shell, native account disclosure, Dashboard hierarchy and responsive customisation surface consume the shared semantic foundation. Logout remains a CSRF-bearing POST form, the existing shell runtime remains the only theme/navigation behaviour owner, and only real permission-filtered module widgets are rendered. No navigation count badges, fabricated search, notifications, routes or records are introduced.


The Fable workspace migration now covers Settings and role administration. Settings retains its module-owned assets and scope-aware persistence; role management retains Auth-owned authorization and mutation behaviour. Both use the shared semantic surfaces without introducing duplicate runtimes or fabricated controls.
