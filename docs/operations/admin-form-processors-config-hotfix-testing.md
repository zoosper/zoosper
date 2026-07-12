# Admin form processors config hotfix testing

Run:

```bash
php -l app/zoosper-page/config/admin_forms.php
php -l tools/verify-admin-form-processors.php
php tools/verify-admin-form-processors.php
php tools/verify-module-admin-form-config-aggregation.php
php tools/verify-admin-form-section-registration.php
php tools/verify-admin-form-section-registry.php
php tools/verify-admin-page-form-sections.php
php tools/verify-editorjs-json-save-pipeline.php
php tools/verify-page-seo-metadata.php
php tools/verify-service-providers.php
```

Expected:

```text
Result: OK
```
