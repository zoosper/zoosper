# Admin grid package extraction

Per-admin grid persistence now belongs to the Marko-native runtime module
`zoosper/admin-grid` under `packages/zoosper-admin-grid`.

The module owns visible-column preferences, named bookmarks, both persistence
tables, the bookmark migration and service bindings. Generic grid definitions,
rendering and CSV remain in the supporting `zoosper/grid` library. Feature grids
remain in their feature modules.

This package is a runtime module because it owns declarative schema, migrations
and service configuration. Its addition increases the compiled module count by
one. Existing tables and data are preserved because ownership moves without
renaming or dropping either table.
