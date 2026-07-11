# Editor.js runtime testing

Run:

```bash
php tools/verify-editorjs-runtime.php
php tools/diagnose-editorjs-runtime.php
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
Editor.js holder appears when the local bundle is loaded.
Textarea remains in the DOM as the submitted source of truth.
Save succeeds and HTML is still sanitised server-side.
Success flash message appears.
```
