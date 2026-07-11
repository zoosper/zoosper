# Editor.js runtime hardening testing

Run:

```bash
php tools/verify-editorjs-runtime.php
php tools/verify-admin-asset-cache-busting.php
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
Editor status says Editor.js ready.
Editor holder is visible.
Textarea is hidden only after Editor.js is ready.
If JS fails, textarea remains visible.
Save still works and shows flash message.
```
