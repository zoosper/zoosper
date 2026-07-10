# Apply Zoosper Phase 0.22 Admin Form Field Injection Update

Apply from repository root:

```bash
unzip zoosper-phase-0.22-admin-form-field-injection-update.zip -d /tmp/zoosper-phase-0.22
cp -R /tmp/zoosper-phase-0.22/zoosper-phase-0.22-admin-form-field-injection-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l config/logging.php
php -l app/zoosper-core/src/Log/ModuleLoggerProviderLoader.php
php -l app/zoosper-admin/src/Form/AdminFormField.php
php -l app/zoosper-admin/src/Form/AdminFormDefinition.php
php -l app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php
php -l app/zoosper-auth/src/Acl/AclTreeBuilder.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
php -l themes/admin/default/templates/components/form/field.php
```

Functional checks:

```bash
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Admin\\Form\\AdminFormUiConfigLoader'));"
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Core\\Log\\ModuleLoggerProviderLoader'));"
```

Browser test:

```text
/admin/roles/edit?id=3
/admin/pages/edit?id=1
```

Expected:

- Permission parent groups sort alphabetically by label.
- Module log filenames are defined in each module's own `config/logging.php`.
- `ApplicationFactory` no longer hard-codes every module logger service line.
```
