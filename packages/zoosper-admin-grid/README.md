# zoosper/admin-grid

Per-admin grid preferences, saved views and persistence integration for Zoosper.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\AdminGrid\` maps to `src/`.

## Architecture

- `src/BulkAction/`
- `src/GridBookmarkRepository.php`
- `src/GridBulkActionManifestRenderer.php`
- `src/GridCompactFilterChipsRenderer.php`
- `src/GridCompactToolbarRenderer.php`
- `src/GridCompactWorkspaceRenderer.php`
- `src/GridFeatureAcceptance.php`
- `src/GridFeatureAcceptanceReport.php`
- `src/GridPreferenceRepository.php`
- `src/GridStateNormaliser.php`
- `src/GridViewMutationService.php`
- `src/GridViewState.php`
- `src/GridViewStateResolver.php`

## Configuration

- `config/admin_assets.php`: Admin asset contributions.
- `config/assets.php`: Runtime asset registration.
- `config/db_schema.php`: Declarative database schema.
- `config/services.php`: Service-container bindings.

## Dependencies

- `ext-pdo`: `*`.
- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.
- `zoosper/grid`: `dev-dev`.
- Development dependencies: `pestphp/pest`, `pestphp/pest-plugin`, `phpunit/phpunit`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202607310002_create_admin_grid_bookmarks.php`.

## Extension points

- `config/admin_assets.php` for Admin assets.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-admin-grid/tests`.
- Current regression files discovered: `60`. Use `find packages/zoosper-admin-grid/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

## Feature-owned workspace URLs

Compact Grid controls derive Clear all from the feature action. Export is an explicit capability and may be disabled when a feature has no real export endpoint.

## HTTP query-state adapter

`AdminCollectionGridQuery` maps flat GET controls into canonical Grid state: feature filters are nested under `filters`, `sort` and `dir` become `sort_by` and `sort_dir`, and array-valued column state is preserved. Query state replaces complete top-level bookmark values so shorter submitted lists cannot retain stale columns.

## Pagination ownership

This package directly consumes the stable `Zoosper\Pagination` request/result boundary through `zoosper/pagination` (`dev-dev`). It must not import `Marko\Pagination` classes.

## Admin visual integration

The final `grid-admin-polish.css` asset is owned by this package and loads at sort order `95`, after the established Grid behaviour styles. It consumes the Admin shell semantic tokens for light and dark presentation while retaining package ownership of Grid layout, tables, panels, actions, saved views, pagination, selection, and export UI. Its semantic Grid tokens are applied to both full `[data-grid-workspace]` compositions and standalone `.grid-table` renderings, so feature-owned scroll wrappers do not invalidate table borders, row states, or theme colours.

The compact filter and column controls publish explicit `aria-controls` and labelled panel relationships. Close controls remain native buttons, visible focus is preserved, mobile panels remain dismissible, and reduced-motion preferences suppress decorative motion. Production renderers contain no inline styles, scripts, event handlers, or unsafe HTML construction.

Visual changes do not alter Grid criteria, query-state persistence, saved-view repositories, bulk-action permissions, POST-only mutation routes, CSRF validation, local-path validation, exports, or audit behaviour. Run the package regression suite and `AdminGridPolishContractTest` after changing the integration layer.

Grid body rows use package-owned, theme-aware odd/even presentation, permanent horizontal separators and restrained vertical cell boundaries so records remain traceable across wide horizontally scrollable tables. A stronger header separator makes the column-heading boundary unambiguous. Hover and keyboard `focus-within` states override the stripe, while selected rows retain a stronger fill and a solid leading-edge marker in addition to their checked control. The state hierarchy applies to cells so it remains visible alongside selection styling, and row transitions follow the existing reduced-motion contract.

The compact toolbar groups display controls, saved-view controls, export, saved state, and per-page selection into one responsive semantic surface. Feature integrations may supply their declared ascending page-size allow-list; existing consumers retain `[20, 50, 100, 200]`. Compact Grid presentation is package-owned: the Admin module retains only generic Grid and column-filter integration and must not reintroduce negative title-overlap positioning or duplicate compact workspace styling. `grid-admin-polish.css` remains the final presentation owner and explicitly neutralises shared primary-button styling for filter-chip remove controls without changing their data attributes or behaviour. At `390px` (`24.375rem`), controls and panels stack, active-filter chips remain compact wrapping pills, and wide tables expose bounded touch and keyboard-compatible horizontal scrolling without changing GET query, paging, or persistence behaviour.


### Toolbar composition

The package-owned compact toolbar presents actions, saved state and page size as one dense semantic surface on desktop, then stacks the same controls below `48rem` and preserves the exact `390px` wrapping contract. Final polish uses Admin theme tokens for controls and keeps pagination centred without changing Grid query, export, saved-view or persistence behaviour.


### Responsive panels and paging

The final package polish keeps clean saved state visually quiet while retaining its live status node for column-order feedback. Filters and Columns use bounded opaque desktop panels and become in-flow disclosures below `45rem`, preventing viewport escape without changing their native controls, ARIA relationships, GET submission or close behaviour. When direct-page enhancement is available, the existing per-page selector is moved beside the page-jump control; without JavaScript it remains available in the toolbar.

### Disclosure and saved-view integration

Filters and Columns are mutually exclusive within their owning workspace: opening one closes the sibling disclosure, resets its `aria-expanded` state, and closes only an adjacent saved-view surface. The compact disclosure runtime is registered through the canonical `assets` map with a content-derived version because that is the registry consumed by rendered Admin pages; the legacy `scripts` list remains package metadata and is not treated as runtime proof. The earlier Admin-owned compact behaviour registration is intentionally absent so a control has exactly one event owner rather than two handlers that cancel each other's toggle. The current-page bulk-action selector also publishes the canonical `bulk_action` field name while remaining disabled until a usable selection and contributed action exist. The standalone saved-view surface owns complete light/dark Grid tokens, an explicitly opaque Admin surface, contained one-column mutation layout, and selectors that match its actual sibling placement after the workspace. These presentation and disclosure changes do not alter GET criteria, POST-only mutation actions, CSRF fields, saved-view repositories, permissions, or persistence semantics. A feature without a rendered mutation-form target continues to hide Manage saved views rather than expose a non-functional write control. Compact panel headers avoid negative edge compensation, and both compact disclosures and saved-view settings use explicit inset spacing, bounded header surfaces and calm internal rhythm. These CSS-only refinements preserve the package runtime as the sole disclosure owner and do not change ARIA, query state, saved-view mutation, permission, CSRF, audit or responsive in-flow contracts.
