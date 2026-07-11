# Editor.js tools testing

Install and rebuild:

```bash
npm install
npm run build:admin-editor
```

Verify:

```bash
php tools/verify-editorjs-tools.php
php tools/verify-editorjs-runtime.php
php tools/verify-admin-editor-build-pipeline.php
php tools/verify-admin-content-editor.php
php tools/verify-project-structure.php
php tools/audit-public-webroot.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/admin/pages/create
/admin/pages/edit?id=1
```

Expected:

```text
Editor.js toolbox includes heading and list tools.
Headings are limited to h2, h3, h4.
Ordered/unordered lists save as sanitised HTML.
```
