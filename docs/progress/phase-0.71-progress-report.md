# Phase 0.71 progress report

## Feature name

Public Theme Asset Cleanup / Admin Asset Path Consolidation.

## Implemented

- Removed hard-coded admin CSS reference to `/themes/admin/default/assets/css/admin.css`.
- Added fallback `public/assets/admin/css/admin.css`.
- Added migration tool to copy legacy public theme assets to canonical published paths.
- Added audit/removal tools for `public/themes`.
- Updated public webroot policy to block `/themes/`.
- Added verification tooling.

## What remains

- Run migration/removal commands and verify structure is green.
- Keep future source theme assets under `themes/` only.
- Publish frontend assets to `public/static/themes/<theme>/...` only.
