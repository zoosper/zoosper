# Compact Grid controls and real saved-view wiring

Phase 7E keeps the Grid table close to the toolbar by collapsing column and saved-view persistence controls behind a native `details` disclosure. Filters and Columns retain their existing on-demand panels.

The compact workspace renderer now passes the real `GridViewState::bookmarks`, active bookmark ID and local form action into the toolbar. This closes the gap where the selector renderer was correct in isolation but received its default empty bookmark argument in the real page.

The persistence forms remain server-rendered POST forms with CSRF fields. This phase does not replace the bookmark repository, mutation service or per-admin/grid isolation.
