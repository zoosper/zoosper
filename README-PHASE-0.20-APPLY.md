# Apply Zoosper Phase 0.20 Controller Thinning and Form Components Update

Apply from repository root:

```bash
unzip zoosper-phase-0.20-controller-thinning-form-components-update.zip -d /tmp/zoosper-phase-0.20
cp -R /tmp/zoosper-phase-0.20/zoosper-phase-0.20-controller-thinning-form-components-update/* .
composer dump-autoload
php bin/zoosper migrate
```

This fixes the `/admin/themes` warning caused by a bad relative `require` path in the admin theme override.

Smoke test:

```bash
php -l themes/admin/default/templates/modules/zoosper-theme/admin/themes/index.php
php -l themes/admin/default/templates/modules/zoosper-admin/audit-log/index.php
php -l themes/admin/default/templates/modules/zoosper-admin/login-history/index.php
php -l app/zoosper-core/src/Log/LocalLogger.php
php -l app/zoosper-core/src/Log/LogManager.php
php -l config/logging.php
php -l themes/admin/default/templates/components/form/input.php
php -l themes/admin/default/templates/components/form/select.php
php -l themes/admin/default/templates/components/form/textarea.php
```

Browser test:

```text
/admin/themes
/admin/audit-log
/admin/login-history
```

Logging note:

The logging classes are added as a foundation. The next dedicated logging phase should wire them into `ApplicationFactory`, error handling and module controller providers.
