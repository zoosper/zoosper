# Final Page Grid cutover

Apply against the latest current Page files, not an old snapshot.

## GET `/admin/pages`

Use the complete compact Grid builder and render exactly one `data-grid-workspace`
container. The live output must include compact toolbar, hidden Filters panel,
hidden Columns panel, applied chips, result summary, table and navigation.

## POST `/admin/pages/grid`

Retain existing authentication, Page permission and CSRF middleware. Delegate to
the existing Grid mutation coordinator and redirect only to `/admin/pages`.

## GET `/admin/pages/export`

Retain authentication and Page permission. Delegate to the audited export request
coordinator and return its secure headers and CSV body.

## Remove after green cutover

- raw text `site_id` filter
- hidden-only `page_size` field
- legacy Page-specific filter renderer
- duplicate Page Grid service binding
- temporary `*.patch*.md` integration files from prior phases
- preview HTML/CSS/JS from production packages

Do not remove the generic `zoosper/grid` table renderer or shared Admin Grid
contracts used by Audit Log and Login History.
