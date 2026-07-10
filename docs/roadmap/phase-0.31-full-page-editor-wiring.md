# Phase 0.31 - Full Page Editor Wiring

Next phase should use the latest `dev` files for full replacement of current working files.

Files likely needed:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
app/zoosper-admin/src/Layout/AdminLayout.php
app/zoosper-admin/src/UI/AdminViewRenderer.php
themes/admin/default/templates/layout.php
app/zoosper-admin/src/Controller/PageAdminController.php
app/zoosper-page/config/controllers.php
app/zoosper-page/resources/views/admin/pages/create.php
app/zoosper-page/resources/views/admin/pages/edit.php
```

Implementation items:

- inject/register `AdminAssetRegistry` and `AdminAssetRenderer`
- render module-owned assets in admin layout
- wire tag selector assets from `zoosper-page`
- replace page site dropdown with tag-style selector
- save `site_ids[]` through `PageStoreViewAssignmentService`
- preserve legacy `site_id` fallback while transitioning
- avoid logging sensitive auth/payment values
