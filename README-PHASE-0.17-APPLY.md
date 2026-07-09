# Apply Zoosper Phase 0.17 Admin Components and Module UI Update

Apply from repository root:

```bash
unzip zoosper-phase-0.17-admin-components-module-ui-update.zip -d /tmp/zoosper-phase-0.17
cp -R /tmp/zoosper-phase-0.17/zoosper-phase-0.17-admin-components-module-ui-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-theme/src/Layout/LayoutUpdate.php
php -l app/zoosper-theme/src/Layout/LayoutUpdateRepository.php
php -l app/zoosper-theme/src/Template/TemplateRenderer.php
php -l app/zoosper-admin/src/UI/AdminViewRenderer.php
php -l app/zoosper-admin/src/UI/AdminComponentRenderer.php
php -l themes/admin/default/templates/layout.php
php -l themes/admin/default/templates/components/table.php
php -l themes/admin/default/templates/components/card.php
php -l themes/admin/default/templates/modules/zoosper-admin/dashboard/index.php
```

Important: apply notes from:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.phase017_patch.md
```

Browser test:

```text
/admin/
/admin/themes
/home
/admin/pages/preview?id=1
```
