# Admin page form section testing

Run:

```bash
php -l app/zoosper-admin/src/Controller/PageAdminController.php
php -l tools/verify-admin-page-form-sections.php
php tools/verify-admin-page-form-sections.php
php tools/verify-editor-interface-contracts.php
php tools/verify-editorjs-json-save-pipeline.php
php tools/verify-page-seo-metadata.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/admin/pages/create
/admin/pages/edit?id=1
/
```

Expected:

```text
The page form is organised into clear sections.
Editor.js still works.
content_json is still captured.
SEO fields are still visible and save correctly.
Frontend rendering remains unchanged.
```
