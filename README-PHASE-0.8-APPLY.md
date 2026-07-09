# Apply Zoosper Phase 0.8 Admin Users/Roles Update

Apply from repository root:

```bash
unzip zoosper-phase-0.8-admin-users-roles-update.zip -d /tmp/zoosper-phase-0.8
cp -R /tmp/zoosper-phase-0.8/zoosper-phase-0.8-admin-users-roles-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-admin/src/Controller/UserAdminController.php
php -l app/zoosper-admin/src/Controller/RoleAdminController.php
php -l app/zoosper-auth/src/Repository/AdminUserRepository.php
php -l app/zoosper-auth/src/Repository/RoleRepository.php
php -l app/zoosper-admin/src/Layout/AdminLayout.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
php -l app/zoosper-core/src/Translation/ModuleTranslationLoader.php
php -l app/zoosper-core/src/Translation/Translator.php
php -l database/migrations/202607090005_seed_user_role_permissions.php
```

Browser test:

```text
/admin/users
/admin/users/create
/admin/roles
/admin/roles/create
```

Set CMS version in `.env` if desired:

```env
CMS_VERSION=0.8.0-dev
```
```
