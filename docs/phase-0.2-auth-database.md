# Phase 0.2 - Auth and database

Adds real database-backed admin users, sessions, login/logout and persistent role/permission tables.

## Commands

```bash
php bin/zoosper migrate
php bin/zoosper admin:create --email=admin@example.com --password='ChangeMe123!' --name='Admin User'
```

## Tables

- migrations
- admin_users
- admin_roles
- admin_permissions
- admin_user_roles
- admin_role_permissions

## Next phase

Add `sites`, `site_domains`, `pages`, `page_revisions`, site resolver and first CMS page rendering.
