# Apply Zoosper Phase 0.13 Theme Template Rendering Update

Apply from repository root:

```bash
unzip zoosper-phase-0.13-theme-template-rendering-update.zip -d /tmp/zoosper-phase-0.13
cp -R /tmp/zoosper-phase-0.13/zoosper-phase-0.13-theme-template-rendering-update/* .
chmod +x bin/zoosper-schema
composer dump-autoload
php bin/zoosper migrate
```

This package also fixes:

```text
PHP Fatal error: Call to undefined function env() in config/app.php
```

Smoke test:

```bash
php -l config/app.php
php -l bin/zoosper-schema
php -l app/zoosper-theme/src/Theme/Theme.php
php -l app/zoosper-theme/src/Theme/ThemeResolver.php
php -l app/zoosper-theme/src/Template/TemplateRenderer.php
php -l app/zoosper-page/src/Service/PageRenderer.php
php -l themes/default/templates/page.php
```

Schema CLI test:

```bash
php bin/zoosper-schema validate
```

Browser test:

```text
/home
/admin/pages/preview?id=1
```

Optional ApplicationFactory wiring:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.phase013_patch.md
```
