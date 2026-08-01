# Phase 3N final Page Grid routes

Replace the legacy index action registration with the complete controller adapter
while preserving the current middleware/permission schema:

```text
GET  /admin/pages        -> complete Grid index
POST /admin/pages/grid   -> CSRF-protected Grid mutation
GET  /admin/pages/export -> audited resolved-view CSV export
```

The GET index must call `PageGridCompletePageBuilder`. The POST route must validate
the existing CSRF token before `PageGridMutationCoordinator`. Export must use
`PageGridExportRequestCoordinator`. No route accepts user ID, Grid key, filename,
repository or class parameters.
