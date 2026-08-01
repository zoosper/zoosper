# Admin grid column order

Before Phase 2M, Zoosper persisted visible-column keys but rendered those columns
in definition order. Drag-and-drop ordering was therefore not supported by the
resolved bookmark state.

Phase 2M adds a `column_order` bookmark field, validates it against the live grid
definition and applies it through `GridColumnOrderer` before visibility filtering.
Unknown, duplicate and retired keys are discarded; newly contributed columns are
appended in definition order.

This phase provides persistence and rendering semantics. A later UI phase can add
accessible drag handles, keyboard move controls and a CSRF-protected save action
that writes `column_order` into the user's named bookmark. No JavaScript drag UI
is claimed by this phase.
