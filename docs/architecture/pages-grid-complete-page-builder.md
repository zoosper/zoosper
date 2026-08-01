# Pages Grid complete page builder

Phase 2W composes the resolved per-admin workspace state, Page data source,
mutation forms and shared Grid renderer into one `GridWorkspacePage`.

`PageGridPageBuilder` resolves state exactly once, queries Pages using that
state's `GridCriteria`, and renders both workspace controls and row results from
the same definition and criteria. This prevents filter, sorting, visibility or
column-order drift between the toolbar and table.

The builder requires an authenticated admin ID and a `GridWorkspaceCsrf` value
created by the host application's existing CSRF service. Endpoints remain fixed:
GET `/admin/pages` for viewing and POST `/admin/pages/grid` for mutations.

The live Page controller can now remain thin: authenticate and authorise, create
the request and CSRF value objects, call the builder for GET, or validate CSRF
and call the mutation coordinator for POST. The template receives the combined
workspace and Grid HTML rather than rebuilding state.
