# Phase 0.32 - Full admin layout asset wiring

Next phase should use latest `dev` files and produce full replacements for:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
app/zoosper-admin/src/Layout/AdminLayout.php
app/zoosper-admin/src/UI/AdminViewRenderer.php
themes/admin/default/templates/layout.php
```

Implementation goals:

- register `AdminAssetRegistry`, `AdminAssetRenderer` and `AssetPathResolver`
- render module-owned CSS/JS in the admin layout
- remove any remaining hard-coded `/admin/css` and `/admin/js` assumptions
- preserve PCI-aware rules by keeping runtime secrets out of asset config/output
