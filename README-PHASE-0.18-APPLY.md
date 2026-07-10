# Apply Zoosper Phase 0.18 Admin Controller View Refactor

Apply from repository root:

```bash
unzip zoosper-phase-0.18-admin-controller-view-refactor-update.zip -d /tmp/zoosper-phase-0.18
cp -R /tmp/zoosper-phase-0.18/zoosper-phase-0.18-admin-controller-view-refactor-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-admin/src/Controller/DashboardController.php
php -l app/zoosper-admin/src/Controller/AuditLogController.php
php -l app/zoosper-admin/src/Controller/LoginHistoryController.php
php -l app/zoosper-admin/src/Controller/ThemeAdminController.php
php -l app/zoosper-admin/config/controllers.php
php -l app/zoosper-theme/config/controllers.php
php -l app/zoosper-admin/resources/views/dashboard/index.php
php -l app/zoosper-admin/resources/views/audit-log/index.php
php -l app/zoosper-admin/resources/views/login-history/index.php
php -l app/zoosper-theme/resources/views/admin/themes/index.php
```

Browser test:

```text
/admin/
/admin/audit-log
/admin/login-history
/admin/themes
```

Expected:

- Dashboard renders through `zoosper-admin::dashboard/index`.
- Audit log renders through `zoosper-admin::audit-log/index`.
- Login history renders through `zoosper-admin::login-history/index`.
- Themes screen renders through `zoosper-theme::admin/themes/index`.
- Controllers still live in their modules via `config/controllers.php`.
