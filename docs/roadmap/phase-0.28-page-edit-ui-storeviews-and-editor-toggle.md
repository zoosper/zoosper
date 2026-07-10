# Phase 0.28 - Page Edit UI Store Views and Editor Toggle

Recommended next phase after fetching latest existing page controller/view files:

- replace single site selector with multi-site/store-view selector
- load selected site IDs from `PageSiteAssignmentRepository`
- save selected site IDs on page create/update
- add optional editor toggle for large content fields
- keep textarea fallback
- add sanitisation strategy for rendered content
- ensure no PCI-sensitive values are stored in page content or logs
