# Store Orders Grid closure

Store Orders is the reference external API Grid adopter. The completed workspace supports remote scope, pagination, bounded page sizes, safe selectable columns, key-driven column ordering, named/default/deletable saved views, per-admin column preferences, native date filters and allowlisted remote result filters.

Workspace mutations use the shared Admin Grid contracts, a module-owned POST route, session-derived administrator identity and CSRF validation. Store Orders never accepts a client-supplied user or Grid identity. Bookmark state is normalised against the current Grid definition, so removed keys are discarded and new columns are merged according to the shared normaliser.

The Node API contract accepts `store_code`, `kiosk_website_id`, `order_id`, `customer`, `status`, `placed_from`, `placed_to`, `page` and `per_page`. Node must return the filtered total. Sorting and export remain disabled until explicit remote contracts are introduced.


## Sorting and current-page export

Store Orders forwards only the declared sortable keys using `sort` and `dir`; unknown keys fail before transport. The Node endpoint owns the actual ordering and should apply a deterministic secondary key.

The existing `Export CSV` workspace action exports the current visible page only. Export uses mapped safe Grid rows, honours visible columns and their current order, and does not claim to export the complete remote filtered result set. Full-result export remains a separate bounded remote contract.
