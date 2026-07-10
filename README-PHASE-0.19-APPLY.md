# Apply Zoosper Phase 0.19 Pages, Users and Roles View Refactor

Apply from repository root:

```bash
unzip zoosper-phase-0.19-pages-users-roles-view-refactor-update.zip -d /tmp/zoosper-phase-0.19
cp -R /tmp/zoosper-phase-0.19/zoosper-phase-0.19-pages-users-roles-view-refactor-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-page/resources/views/admin/pages/index.php
php -l app/zoosper-page/resources/views/admin/pages/form.php
php -l app/zoosper-auth/resources/views/admin/users/index.php
php -l app/zoosper-auth/resources/views/admin/users/form.php
php -l app/zoosper-auth/resources/views/admin/roles/index.php
php -l app/zoosper-auth/resources/views/admin/roles/form.php
php -l themes/admin/default/templates/components/error.php
php -l themes/admin/default/templates/components/actions.php
php -l themes/admin/default/templates/components/checkbox-list.php
```

Browser regression test:

```text
/admin/pages
/admin/pages/create
/admin/users
/admin/users/create
/admin/roles
/admin/roles/create
```

Important:

This package adds the module view layer and reusable admin components. It intentionally keeps controller mapping in each module. Apply controller constructor/view-renderer changes in modules only, following:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.phase019_patch.md
```

That avoids growing `ApplicationFactory` again.
