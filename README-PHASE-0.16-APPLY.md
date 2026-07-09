# Apply Zoosper Phase 0.16 Module Template Overrides and Admin Theme Update

Apply from repository root:

```bash
unzip zoosper-phase-0.16-module-template-admin-theme-update.zip -d /tmp/zoosper-phase-0.16
cp -R /tmp/zoosper-phase-0.16/zoosper-phase-0.16-module-template-admin-theme-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Container/ServiceContainer.php
php -l app/zoosper-core/src/Routing/ControllerProviderLoader.php
php -l app/zoosper-theme/src/Template/TemplateRenderer.php
php -l app/zoosper-admin/src/Layout/AdminLayout.php
php -l app/zoosper-page/src/Service/PageRenderer.php
php -l app/zoosper-theme/config/controllers.php
php -l themes/admin/default/templates/layout.php
php -l themes/default/templates/modules/zoosper-page/page/view.php
```

Browser test:

```text
/home
/admin/
/admin/themes
/admin/pages/preview?id=1
```

Important: apply the wiring notes from:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.phase016_patch.md
```

That lets future modules provide controllers without editing `ApplicationFactory`.
