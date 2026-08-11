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
