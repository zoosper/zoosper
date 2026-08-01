# Admin Grid navigation rendering

Phase 3F converts the query-state work into reusable navigation objects and
accessible links. Page navigation is built from the same resolved state used by
the Page query, Grid renderer and CSV export.

Previous and Next links exist only when applicable. Sort URLs are generated only
for columns declared sortable by the live definition. The current sorted column
toggles direction; other sortable columns begin ascending. Export is a real link
to the Page-owned endpoint rather than an inert button.

Every URL remains application-local and preserves filters, Site multiselection,
bookmarks, visible columns and column order through `GridWorkspaceQuery`.
