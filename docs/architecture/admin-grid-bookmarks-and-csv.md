# Admin grid bookmarks and CSV foundation

Zoosper already stores one visible-column preference per admin user and grid in
`admin_grid_preferences`. Phase 2F adds named saved views in
`admin_grid_bookmarks`, allowing multiple per-user, per-grid states and one
optional default bookmark.

Bookmark state is JSON so grids can persist filters, sorting, page size and
visible-column keys without coupling the repository to one screen. Repository
queries always scope by both `admin_user_id` and `grid_key`.

`GridCsvExporter` exports rows from a `GridDefinition`, honours selected or
default-visible columns, and always excludes the HTML actions column. This phase
provides the reusable services and schema. HTTP routes, permission checks,
streaming response headers and screen controls must be wired during each grid's
UI cutover rather than exposed globally without authorisation review.
