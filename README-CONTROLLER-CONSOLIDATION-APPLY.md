# Apply Controller Provider Consolidation

Apply from repository root:

```bash
unzip zoosper-controller-provider-consolidation-update.zip -d /tmp/zoosper-controller-provider
cp -R /tmp/zoosper-controller-provider/zoosper-controller-provider-consolidation-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Container/ServiceContainer.php
php -l app/zoosper-core/src/Routing/ControllerProviderLoader.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
php -l app/zoosper-admin/config/controllers.php
php -l app/zoosper-auth/config/controllers.php
php -l app/zoosper-page/config/controllers.php
php -l app/zoosper-api/config/controllers.php
php -l app/zoosper-theme/config/controllers.php
```

Autoload check:

```bash
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Core\\Routing\\ControllerProviderLoader'));"
```

Browser/API test:

```text
/admin/
/admin/pages
/admin/users
/admin/roles
/admin/themes
/admin/audit-log
/admin/login-history
/api/v1/health
/home
```

Expected result: `ApplicationFactory.php` no longer manually creates every admin/API controller; module configs do.
