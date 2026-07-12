# Admin/site locale resolution testing

Run:

```bash
php -l config/i18n.php
php -l app/zoosper-core/src/I18n/LocaleResolution.php
php -l app/zoosper-core/src/I18n/LocaleResolverInterface.php
php -l app/zoosper-core/src/I18n/ConfiguredLocaleResolver.php
php -l tools/verify-admin-site-locale-resolution.php
php tools/verify-admin-site-locale-resolution.php
php tools/verify-translatable-admin-system-messages.php
php tools/verify-admin-translator-resolution.php
php tools/verify-translation-file-aggregator-comment-safety.php
php tools/verify-module-owned-translation-file-aggregation.php
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
/admin/pages
/
```

Expected: no visible change. This is a locale resolver foundation phase.
