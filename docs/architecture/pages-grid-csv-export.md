# Pages Grid CSV export

Phase 2X adds a bounded CSV export pipeline for resolved Grid views. Export uses
the live `GridViewState`, so selected filters, column visibility and column order
remain aligned with the screen. The generic `GridCsvExporter` continues to own
spreadsheet-formula neutralisation and excludes the Actions column.

`GridWorkspaceExportPolicy` enforces a server-side row ceiling. The result reports
whether rows were truncated and supplies private, no-store, nosniff download
headers with a sanitised filename. The Pages coordinator fixes the filename to
`pages.csv`.

The eventual HTTP export route must authenticate the administrator, enforce the
Page export permission, resolve `admin.pages` on the server, query rows using the
same resolved criteria, and audit the export. It must not accept a repository,
class, user ID, arbitrary Grid key or filename from the browser.
