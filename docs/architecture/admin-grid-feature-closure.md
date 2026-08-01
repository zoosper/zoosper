# Admin Grid feature closure

The Grid feature closes when the actual authenticated Pages response satisfies the
compact workspace acceptance contract, all tests are green, legacy Page Grid
markup is removed, CSV export remains bounded and audited, and one additional
existing Grid demonstrates shared package reuse.

## Required Pages behaviour

- compact Filters and Columns toggles
- filters closed by default
- named multi-Site filtering
- removable applied-filter chips
- visible page-size selector
- configurable and ordered columns
- ID and Actions locked
- per-admin saved and default views
- Saved / Unsaved state
- protected CSRF mutations
- state-preserving sorting and pagination
- secure CSV export without Actions

## Reuse proof

Adopt the compact toolbar, page-size selector and filter chips on Audit Log or
Login History without copying Page-specific code. Feature-specific filters,
permissions and repositories remain owned by their modules.

## Deferred backlog

Mass actions, inline editing, AJAX data loading, column resizing and advanced
filter operators are explicitly outside this closure and should be planned as a
later enhancement phase.
