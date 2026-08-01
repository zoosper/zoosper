# Admin Grid view status

Phase 3I converts deterministic dirty-state detection into a visible, accessible
workspace status. The resolver finds the already user-scoped active bookmark,
compares its stored normalised state with the current resolved state, and emits
`Default view`, the saved view name, and optionally `Unsaved changes`.

The status is server-authoritative. Missing or stale bookmark IDs safely fall back
to Default view. The renderer escapes the view label and uses `role=status` for
the unsaved-change message. Styling is module-owned and supports increased
contrast without requiring JavaScript.
