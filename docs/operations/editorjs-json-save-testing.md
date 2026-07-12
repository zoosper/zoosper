# Editor.js JSON save testing

Run:

```bash
php tools/verify-editorjs-json-save-pipeline.php
php tools/diagnose-editorjs-json-save-pipeline.php
php tools/verify-block-json-content-model.php
php tools/verify-page-dual-content-hydration.php
php tools/verify-page-seo-metadata.php
php tools/verify-frontend-page-view-noescape.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/admin/pages/edit?id=1
/admin/pages/create
/
```

Expected:

```text
Editor.js still works.
SEO section remains visible.
Save succeeds.
pages.content_json becomes populated after saving with Editor.js active.
pages.content_format remains html.
Homepage still renders from sanitised HTML fallback.
```
