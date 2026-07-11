# Phase 0.71 - Public Theme Asset Cleanup / Admin Asset Path Consolidation

Zoosper now treats `themes/` as source-only and `public/assets/` or `public/static/` as published assets.

## Rules

```text
themes/                         source templates/theme files
public/assets/admin/            published admin assets
public/static/themes/<theme>/   published frontend theme static assets
public/themes/                  forbidden
```

The admin layout no longer hard-codes `/themes/admin/default/assets/css/admin.css`; it uses the admin asset registry instead.
