# Admin Grid active-view status

Phase 3I turns deterministic dirty-state detection into a reusable presentation
contract. The resolver identifies the active user-owned bookmark from the
resolved bookmark list, compares its stored state with the current resolved Grid
state, and returns a small immutable status object.

The renderer displays the view name, Default badge and an accessible
`Unsaved changes` status where applicable. Bookmark names are escaped. The status
is calculated on the server; no browser-provided dirty flag is trusted.

This gives Pages, Audit Log and Login History the same modern feedback pattern
without coupling the Grid package to a template engine or feature controller.
