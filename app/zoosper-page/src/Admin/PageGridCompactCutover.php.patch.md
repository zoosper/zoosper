# Apply compact workspace to Pages

Update the live Pages workspace output to:

1. Add `data-grid-workspace` to the outer Grid container.
2. Render `GridCompactToolbarRenderer` after the Create page toolbar.
3. Wrap existing filters in `<section class="grid-compact-panel" data-grid-panel="filters" hidden>` and add `data-grid-filter-form` to the GET form.
4. Replace raw Site ID with the existing named multi-Site renderer using `site_id[]`.
5. Wrap column selection in `<section class="grid-compact-panel" data-grid-panel="columns" hidden>`.
6. Render `GridCompactFilterChipsRenderer` immediately before the result summary.
7. Remove the hidden-only page-size control; preserve one canonical `page_size` field or bind the toolbar select directly to the GET form.
8. Keep the table, summary and pagination immediately after chips so they stay above the fold.
9. Keep Grid mutation forms POST-only and CSRF protected.
10. Keep Export CSV on the existing audited export route.

Do not retain the legacy filter block beside this workspace.
