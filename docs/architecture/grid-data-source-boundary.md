# Grid data-source boundary

## Purpose

The Grid renderer and admin workspace must not depend on where collection rows originate. Phase 5A introduces a small transport-neutral boundary in `zoosper-grid` for database repositories, external APIs, search services and future collection providers.

## Contracts

- `GridDataSourceInterface` exposes capabilities and fetches a result.
- `GridQuery` carries pagination, sorting, filters, search and an optional cursor without HTTP parameter names.
- `GridResult` carries rows and either numbered or cursor pagination metadata.
- `GridDataSourceCapabilities` allows the page builder to render only controls that the source can honour for the entire collection.
- `GridPaginationMode` distinguishes numbered and cursor collections explicitly.

## Boundaries

These contracts contain no HTTP client, database connection, HTML, authentication, endpoint URL or external response shape. `zoosper-api-grid` will adapt API transport and mapping to these contracts in the following phase. Feature modules remain responsible for endpoint-specific mapping, permissions and trusted scope.

A source must not advertise sorting, searching, filtering or export unless the backing collection implements that operation across the complete result set. Client-side filtering of one remote page is not a supported substitute.
