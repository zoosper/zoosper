# Phase 0.30 - Wire Admin Assets and Page Store-view Tags

Recommended next phase after fetching latest `dev` files:

- inject `AdminAssetRegistry` into the admin layout/rendering layer
- render module-owned CSS/JS with `admin-assets.php`
- wire tag selector assets from `zoosper-page`
- update page create/edit forms to use the tag selector for `site_ids[]`
- save assignments via `PageSiteAssignmentRepository`
- keep textarea fallback and avoid storing PCI-sensitive values in content fields or logs

Files likely required for full replacement:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
app/zoosper-admin/src/Layout/AdminLayout.php
app/zoosper-admin/src/UI/AdminViewRenderer.php
themes/admin/default/templates/layout.php
app/zoosper-admin/src/Controller/PageAdminController.php
app/zoosper-page/config/controllers.php
app/zoosper-page/resources/views/admin/pages/*.php
```
