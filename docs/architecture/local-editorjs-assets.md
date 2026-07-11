# Phase 0.69 - Local Editor.js Asset Bundling Foundation

Zoosper now has a package-managed path for bundling Editor.js locally instead of relying on CDN or manually copied vendor files.

## Files

```text
package.json
vite.admin-editor.config.js
assets/admin/editor/zoosper-editorjs-entry.js
public/assets/admin/js/editorjs.bundle.js
```

## Design

`node_modules` stays outside `public/`. Only the built bundle is intentionally published under `public/assets/admin/js`.

Phase 0.69 keeps the textarea as source of truth. Real Editor.js block editing and `block_json` persistence are deferred.
