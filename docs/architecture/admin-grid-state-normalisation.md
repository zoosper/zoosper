# Admin grid state normalisation

Saved views and submitted grid settings are untrusted state. They may reference
columns or filters removed by a module update, contain invalid sorting keys, or
request unreasonable page sizes.

`GridStateNormaliser` in `zoosper/admin-grid` validates state against the current
`GridDefinition`. It discards unknown filters and columns, accepts only declared
sortable keys, normalises direction, bounds page size to 5 through 200, removes
duplicate columns and restores every non-toggleable column.

The service can also produce `GridCriteria`, giving Pages, Audit Log and Login
History one canonical state-to-query path when bookmark and preference controls
are wired into their controllers.
