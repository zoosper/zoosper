# Pages Grid thin controller adapter

Phase 3B adds the final framework-neutral adapter for the three Page Grid paths:
index, mutation and CSV export. The host controller remains responsible for the
existing authentication, Page permission, CSRF and flash-message services.

The adapter returns `PageGridResponse` payloads rather than depending on a
concrete response implementation. Mutation responses use HTTP 303 and only local
paths. CSV responses use the secure headers from `GridWorkspaceExportResult`.

`PageGridExportRequestCoordinator` resolves the per-admin view exactly once and
uses the same `GridCriteria` for export rows and audit context. The Page-owned
export data source interface prevents the generic Admin Grid module from knowing
about Page repositories.
