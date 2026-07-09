# Apply Zoosper Phase 0.7 Dynamic Routes Update

Apply from repository root:

```bash
unzip zoosper-phase-0.7-dynamic-routes-update.zip -d /tmp/zoosper-phase-0.7
cp -R /tmp/zoosper-phase-0.7/zoosper-phase-0.7-dynamic-routes-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Routing/ModuleRouteDefinition.php
php -l app/zoosper-core/src/Routing/ModuleRouteLoader.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
php -l app/zoosper-admin/config/admin_routes.php
php -l app/zoosper-page/config/admin_routes.php
php -l app/zoosper-api/config/api_routes.php
```

Browser/API test:

```text
/admin
/admin/login
/admin/pages
/admin/pages/create
/api/v1/health
/api/v1/content/page?slug=home
/
/home
```

Expected result: routes still work, but they are now discovered from module config.
