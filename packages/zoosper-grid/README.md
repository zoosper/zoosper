# zoosper/grid

Reusable grid definitions, criteria, rendering, extension registry and CSV export for Zoosper.

## Responsibilities

- Composer type: `library`.
- Namespace `Zoosper\Grid\` maps to `src/`.

## Architecture

- `src/BulkAction/`
- `src/DataSource/`
- `src/GridColumn.php`
- `src/GridColumnOrderer.php`
- `src/GridColumnRegistry.php`
- `src/GridCriteria.php`
- `src/GridCsvExporter.php`
- `src/GridDataSourceInterface.php`
- `src/GridDefinition.php`
- `src/GridFilter.php`
- `src/GridFilterOption.php`
- `src/GridFilterValue.php`
- `src/GridHtmlRenderer.php`
- `src/GridMultiselectRenderer.php`

## Dependencies

- `php`: `^8.5`.
- `zoosper/pagination`: `dev-dev`.
- Development dependencies: `pestphp/pest`, `pestphp/pest-plugin`, `phpunit/phpunit`.

## Pagination ownership

The legacy numbered Grid boundary consumes `Zoosper\Pagination\Pager` and `PaginationResult`. Marko pagination classes must remain behind `zoosper/pagination`. The separate neutral `DataSource\GridDataSourceInterface` remains unchanged.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-grid/tests`.
- Current regression files discovered: `18`. Use `find packages/zoosper-grid/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
