# Apply Zoosper Phase 0.15 Theme Admin and Template Overrides Update

Apply from repository root:

```bash
unzip zoosper-phase-0.15-theme-admin-overrides-update.zip -d /tmp/zoosper-phase-0.15
cp -R /tmp/zoosper-phase-0.15/zoosper-phase-0.15-theme-admin-overrides-update/* .
php tools/phase015-fix-composer-autoload.php
composer dump-autoload
php bin/zoosper migrate
```

This fixes:

```text
Class "Zoosper\Theme\Template\TemplateRenderer" not found
```

Smoke test:

```bash
php -l tools/phase015-fix-composer-autoload.php
php -l app/zoosper-theme/src/Theme/ThemeRepository.php
php -l app/zoosper-theme/src/Template/TemplateRenderer.php
php -l app/zoosper-admin/src/Controller/ThemeAdminController.php
php -l app/zoosper-theme/config/admin_menu.php
php -l app/zoosper-theme/config/admin_routes.php
```

Browser test:

```text
/admin/themes
/home
/admin/pages/preview?id=1
```

Also apply the wiring notes from:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.phase015_patch.md
```
