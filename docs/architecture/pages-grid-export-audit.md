# Pages Grid export audit

Phase 2Y adds an optional audit seam around bounded CSV export. The audit record
contains the authenticated administrator ID, fixed Grid key, safe filename,
actual exported row count, truncation flag, resolved filters and visible columns.
It is recorded only after the export result is successfully created.

`GridWorkspaceExportAuditorInterface` keeps `zoosper/admin-grid` independent of a
concrete audit implementation. Hosts may bind the interface to the existing
audit logger; `NullGridWorkspaceExportAuditor` is the explicit no-op fallback.

The Pages coordinator fixes identity to `admin.pages` and `pages.csv`. The live
HTTP route must still enforce the Page export permission and resolve the current
view server-side before querying rows. The browser cannot choose the audited user,
Grid key, filename or repository.
