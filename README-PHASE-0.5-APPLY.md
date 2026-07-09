# Apply Zoosper Phase 0.5 Admin Navigation Update

This package contains update files for:

- shared admin layout
- permission-aware admin menu
- dashboard card navigation
- page admin pages using the shared layout
- migration warning helper script

Apply from repository root:

```bash
unzip zoosper-phase-0.5-admin-nav-update.zip -d /tmp/zoosper-phase-0.5
cp -R /tmp/zoosper-phase-0.5/zoosper-phase-0.5-admin-nav-update/* .
chmod +x tools/fix-migration-pdo-warnings.sh
./tools/fix-migration-pdo-warnings.sh
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-admin/src/Layout/AdminLayout.php
php -l app/zoosper-admin/src/Navigation/AdminMenu.php
php -l app/zoosper-admin/src/Controller/DashboardController.php
php -l app/zoosper-admin/src/Controller/PageAdminController.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
```

Browser test:

```text
/admin
/admin/pages
/admin/pages/create
/admin/pages/edit?id=1
/admin/pages/preview?id=1
```
