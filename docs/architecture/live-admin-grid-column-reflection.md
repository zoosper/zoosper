# Live admin grid column reflection

The application-owned drag bridge now has a small additive companion that mirrors the Columns panel order into the visible table immediately.

## Contract

- Column identity comes from `data-column-key` in the panel and `data-grid-column` in the table.
- Header and body cells move together.
- Existing drag rules remain responsible for pinning ID first and Actions last.
- Reordering marks the current view as unsaved.
- Apply columns and Save view remain responsible for persistence.
- The reflection asset loads after the drag bridge.

The runtime also assigns missing header keys from the initial server-rendered order. This keeps the current Page grid safe while keyed header rendering is normalised separately.
