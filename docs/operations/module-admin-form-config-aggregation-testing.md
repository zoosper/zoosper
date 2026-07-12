# Module admin form config aggregation testing

Run:

```bash
php -l config/admin_forms.php
php -l app/zoosper-page/config/admin_forms.php
php -l app/zoosper-admin/src/Form/AdminFormConfigAggregator.php
php -l app/zoosper-admin/src/Form/AdminFormConfigProviderFactory.php
php -l app/zoosper-admin/src/Controller/PageAdminController.php
php -l tools/verify-module-admin-form-config-aggregation.php
php -l tools/verify-admin-form-section-registration.php
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
