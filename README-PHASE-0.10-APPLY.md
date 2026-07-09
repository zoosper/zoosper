# Apply Zoosper Phase 0.10 ACL Tree and Role Users Update

Apply from repository root:

```bash
unzip zoosper-phase-0.10-acl-tree-role-users-update.zip -d /tmp/zoosper-phase-0.10
cp -R /tmp/zoosper-phase-0.10/zoosper-phase-0.10-acl-tree-role-users-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l database/migrations/202607090007_acl_tree_metadata.php
php -l app/zoosper-auth/src/Acl/AclGroup.php
php -l app/zoosper-auth/src/Acl/AclTreeBuilder.php
php -l app/zoosper-auth/src/Repository/RoleRepository.php
php -l app/zoosper-auth/src/Repository/AdminUserRepository.php
php -l app/zoosper-admin/src/Controller/RoleAdminController.php
```

Browser test:

```text
/admin/roles/edit?id=1
```

Expected:

- Permission Tree grouped by Content, Users, System.
- Assigned Users section with search/filter.
- Saving role updates permissions and role-user assignments.

Also update `ApplicationFactory` using:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.phase010_patch.md
```
