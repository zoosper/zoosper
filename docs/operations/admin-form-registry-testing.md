# Admin form section registry testing

Run:

```bash
php -l app/zoosper-admin/src/Form/AdminFormSection.php
php -l app/zoosper-admin/src/Form/AdminFormSectionProviderInterface.php
php -l app/zoosper-admin/src/Form/AdminFormProviderRegistry.php
php -l app/zoosper-admin/src/Form/AdminFormRenderer.php
php -l app/zoosper-admin/src/Controller/PageAdminController.php
php -l tools/verify-admin-form-section-registry.php
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
