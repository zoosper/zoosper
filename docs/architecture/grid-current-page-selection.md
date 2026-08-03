# Current-page Grid selection foundation

The shared Admin Grid progressively adds row checkboxes only when the first table column is explicitly `ID` and every rendered row has a unique, non-empty identity. Selection is intentionally limited to the current rendered page and is cleared by navigation or refresh.

The selection status bar reports the selected count, supports page-level select all with an indeterminate state, and provides Clear selection. The Bulk actions control is visible but has no executable action in this phase. This prevents the UI from implying that remote or destructive operations are supported before server-side action, permission, CSRF, audit and partial-failure contracts exist.

Feature modules do not own selection JavaScript or CSS. A later phase should replace DOM identity discovery with an explicit server-rendered row-identity contract in `zoosper-grid`, then register the first safe action, Export selected.
