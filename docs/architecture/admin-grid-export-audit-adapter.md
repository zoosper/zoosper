# Admin Grid export audit adapter

Phase 2Z connects the export-audit seam to the existing Zoosper audit capability
without introducing a package dependency from `zoosper/admin-grid` to
`zoosper/admin`.

The Grid package owns a minimal `GridWorkspaceAuditLoggerInterface` and adapts it
to `GridWorkspaceExportAuditorInterface`. The Admin module owns
`AdminGridAuditLoggerBridge`, which delegates to the existing Core
`AuditLoggerInterface::logAction()` contract. When no host logger is available,
the factory returns the explicit null auditor.

The structured action is `admin_grid.export`. Context contains authenticated
admin ID, fixed Grid key, safe filename, actual exported row count, truncation,
resolved filters and visible columns. CSV body and row data are not logged.

The latest Admin service configuration should bind the bridge only when the
existing audit logger exists, then provide the resulting auditor to the audited
CSV export service. This preserves optional-module behaviour and the established
interface-first decoupling pattern.
