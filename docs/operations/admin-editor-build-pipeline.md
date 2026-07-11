# Admin editor build pipeline

Phase 0.69 introduced a Vite build for the local Editor.js bundle. Phase 0.70 fixes the recursive public copy problem by setting:

```js
publicDir: false
```

Run cleanup if the failed build created recursive artefacts:

```bash
php tools/clean-admin-editor-build-artifacts.php
```

Then build:

```bash
npm run build:admin-editor
```

Verify:

```bash
php tools/verify-admin-editor-build-pipeline.php
php tools/verify-project-structure.php
php tools/audit-public-webroot.php
```
