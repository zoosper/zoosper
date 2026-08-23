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
- Current regression files discovered: `59`. Use `find packages/zoosper-admin-grid/tests -type f -name '*Test.php' | sort` for the live list.
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

The final `grid-admin-polish.css` asset is owned by this package and loads at sort order `95`, after the established Grid behaviour styles. It consumes the Admin shell semantic tokens for light and dark presentation while retaining package ownership of Grid layout, tables, panels, actions, saved views, pagination, selection, and export UI.

The compact filter and column controls publish explicit `aria-controls` and labelled panel relationships. Close controls remain native buttons, visible focus is preserved, mobile panels remain dismissible, and reduced-motion preferences suppress decorative motion. Production renderers contain no inline styles, scripts, event handlers, or unsafe HTML construction.

Visual changes do not alter Grid criteria, query-state persistence, saved-view repositories, bulk-action permissions, POST-only mutation routes, CSRF validation, local-path validation, exports, or audit behaviour. Run the package regression suite and `AdminGridPolishContractTest` after changing the integration layer.

Grid body rows use package-owned, theme-aware odd/even presentation and permanent horizontal separators so records remain traceable across wide horizontally scrollable tables. A stronger header separator makes the column-heading boundary unambiguous. Hover and keyboard `focus-within` states override the stripe, while selected rows retain a stronger fill and a solid leading-edge marker in addition to their checked control. The state hierarchy applies to cells so it remains visible alongside selection styling, and row transitions follow the existing reduced-motion contract.
