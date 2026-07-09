# Apply Zoosper Phase 0.6 Dynamic Menu Update

This update adds module-discovered admin menu entries.

Apply from repository root:

```bash
unzip zoosper-phase-0.6-dynamic-menu-update.zip -d /tmp/zoosper-phase-0.6
cp -R /tmp/zoosper-phase-0.6/zoosper-phase-0.6-dynamic-menu-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Module/ModuleDefinition.php
php -l app/zoosper-core/src/Module/ModuleRegistry.php
php -l app/zoosper-admin/src/Navigation/AdminMenuLoader.php
php -l app/zoosper-admin/src/Navigation/AdminMenu.php
php -l app/zoosper-admin/src/Layout/AdminLayout.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
```

Browser test:

```text
/admin
/admin/pages
```

Expected admin menu groups:

```text
Content
  Dashboard
  Pages
System
  Site Domains
  Sites
  Settings
```

Note: this phase discovers menu entries only. Dynamic route registration should be handled in the next phase.
