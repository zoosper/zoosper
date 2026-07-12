# Admin translator resolution testing

Run:

```bash
php -l config/i18n.php
php -l app/zoosper-core/src/I18n/TranslationResolver.php
php -l app/zoosper-admin/src/Controller/PageAdminController.php
php -l tools/verify-admin-translator-resolution.php
php tools/verify-admin-translator-resolution.php
php tools/verify-translation-file-aggregator-comment-safety.php
php tools/verify-module-owned-translation-file-aggregation.php
php tools/verify-translatable-admin-system-messages.php
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

Expected: messages still appear in English, but fallback admin runtime translation now uses the module-owned catalogue path.
