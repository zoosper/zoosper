# Phase 0.8 - Admin Users, Roles and Permission Matrix

This is the next important admin capability.

## Why

Creating users and editing roles from CLI or seed files is not sustainable. Zoosper needs a browser-based admin for this.

## Proposed routes

```text
GET  /admin/users
GET  /admin/users/create
POST /admin/users/create
GET  /admin/users/edit?id=1
POST /admin/users/edit?id=1
POST /admin/users/disable?id=1
POST /admin/users/enable?id=1

GET  /admin/roles
GET  /admin/roles/create
POST /admin/roles/create
GET  /admin/roles/edit?id=1
POST /admin/roles/edit?id=1
```

## Proposed rules

- require `user.manage` for admin user management
- require `role.manage` for role and permission management
- store permissions in `admin_permissions`
- assign permissions through `admin_role_permissions`
- assign users to roles through `admin_user_roles`
- prevent disabling the currently logged-in super admin until another super admin exists
- never display password hashes
- force password hashing through `PasswordHasher`
