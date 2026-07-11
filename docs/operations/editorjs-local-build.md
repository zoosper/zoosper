# Editor.js local build

Install dependencies:

```bash
npm install
```

Build the local admin editor bundle:

```bash
npm run build:admin-editor
```

Verify:

```bash
php tools/verify-editorjs-assets.php
php tools/diagnose-editorjs-assets.php
php tools/verify-editorjs-public-safety.php
php tools/audit-public-webroot.php
```

The placeholder `public/assets/admin/js/editorjs.bundle.js` prevents browser 404s before the first npm build. The npm build should replace it with the local Editor.js bundle.
