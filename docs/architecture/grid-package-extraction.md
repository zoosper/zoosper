# Grid package extraction

Generic grid capability now belongs to the Composer library `zoosper/grid` in
`packages/zoosper-grid`. Its public namespace is `Zoosper\Grid`.

The package owns columns, filters, definitions, criteria, the data-source
contract, module contribution discovery, HTML rendering and CSV export. It
requires `zoosper/core` only for shared pagination and module discovery.

Admin-specific persistence remains in `zoosper-admin`:

- `GridPreferenceRepository`;
- `GridBookmarkRepository`;
- `admin_grid_preferences`;
- `admin_grid_bookmarks`.

Feature-specific definitions, repositories and adapters remain in their feature
modules. This avoids making the generic package depend on admin authentication,
page models or audit tables.

`zoosper/grid` is a Composer library rather than a runtime module because it
owns no routes, service configuration, schema or module bootstrap file.
