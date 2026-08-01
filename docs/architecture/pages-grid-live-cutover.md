# Pages Grid live cutover

Phase 3M combines the page-size selector with the live Pages cutover contract.
The selector offers server-allow-listed values 20, 50, 100 and 200, participates
in saved views, survives navigation, and resets to page one when changed.

All accumulated Grid changes become visible when the current `/admin/pages`
controller and template switch from the legacy block to
`PageGridCompletePageBuilder`. The POST mutation and GET export routes must be
wired at the same cutover so visible controls are functional. The legacy filter
and pagination implementation should then be removed rather than retained as a
second hidden path.

This phase provides an explicit integration checklist rather than replacing the
live controller from an older snapshot. That protects current authentication,
permission, CSRF, layout and constructor contracts.
