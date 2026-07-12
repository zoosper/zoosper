# Admin form processors page save flow testing

Run:

```bash
php -l app/zoosper-admin/src/Controller/PageAdminController.php
php -l tools/verify-admin-form-processor-page-save-flow.php
php tools/verify-admin-form-processor-page-save-flow.php
php tools/verify-admin-form-config-aggregator-empty-handles.php
php tools/verify-admin-form-processors.php
php tools/verify-module-admin-form-config-aggregation.php
php tools/verify-admin-form-section-registration.php
php tools/verify-admin-form-section-registry.php
php tools/verify-admin-page-form-sections.php
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
Core page save behaviour remains unchanged when no processors are registered.
If a future processor returns errors, the page form should redisplay and the page should not save.
```
