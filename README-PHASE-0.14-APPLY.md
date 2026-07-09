# Apply Zoosper Phase 0.14 Site Theme Selection and Layouts Update

Apply from repository root:

```bash
unzip zoosper-phase-0.14-site-theme-layouts-update.zip -d /tmp/zoosper-phase-0.14
cp -R /tmp/zoosper-phase-0.14/zoosper-phase-0.14-site-theme-layouts-update/* .
composer dump-autoload
php bin/zoosper migrate
php bin/zoosper-schema validate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Bootstrap/EnvLoader.php
php -l config/app.php
php -l database/migrations/202607090008_site_theme_code.php
php -l app/zoosper-site/src/Model/Site.php
php -l app/zoosper-site/src/Repository/SiteRepository.php
php -l app/zoosper-theme/src/Template/TemplateRenderer.php
php -l app/zoosper-page/src/Service/PageRenderer.php
php -l themes/default/templates/layout.php
php -l themes/default/templates/page.php
php -l themes/default/templates/partials/header.php
php -l themes/default/templates/partials/footer.php
```

Browser test:

```text
/home
/admin/pages/preview?id=1
```

Expected:

- IDE can resolve `Zoosper\Core\Bootstrap\EnvLoader`.
- Page render uses site `theme_code`.
- Page content is wrapped by `layout.php`.
- Header/footer render from partial templates.
