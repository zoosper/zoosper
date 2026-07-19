# Phase 1.37m.2 - Media schema runtime diagnostic

## Goal

Make the browser-discovered missing `media_assets` table failure easy to diagnose and fix.

## Diagnosis

Editor.js upload reached `MediaEditorJsUploadController`, validation/storage proceeded, but metadata persistence failed because the live database lacked the `media_assets` table.

This means the code path is wired, but the database migration has not been applied to the current environment.

## Implemented

- Added `tools/diagnose-media-schema-runtime.php`.
- Added an operations document explaining the migration fix.
- Added a tiny regression test ensuring the diagnostic remains discoverable and points to the migration command.

## Manual fix

```bash
PHP=php8.5 bin/zoosper migrate
php8.5 tools/diagnose-media-schema-runtime.php
PHP=php8.5 bin/verify
```
