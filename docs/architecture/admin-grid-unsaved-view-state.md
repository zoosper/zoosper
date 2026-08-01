# Admin Grid unsaved-view state

Phase 3H adds deterministic dirty-state detection for named Grid views. The
fingerprint covers filters, sorting, page size, visible columns and column order.
It deliberately excludes the current page, so ordinary pagination does not mark a
saved view as modified.

This supports a modern workspace interaction: after an administrator changes a
filter, hides a column or reorders columns, the UI can show `Unsaved changes`
beside the active view and emphasise Save View. The comparison is server-owned and
uses normalised resolved state rather than trusting a browser-provided dirty flag.

Filter-map keys are sorted before hashing so equivalent states do not differ only
because request or JSON key order changed. List order remains significant for
multi-select values, visible columns and column order.
