# Admin Users, Roles and Permission Matrix

Phase 0.8 adds browser-based management for admin users, roles and permissions.

## Added routes

```text
GET  /admin/users
GET  /admin/users/create
POST /admin/users/create
GET  /admin/users/edit?id=1
POST /admin/users/edit?id=1

GET  /admin/roles
GET  /admin/roles/create
POST /admin/roles/create
GET  /admin/roles/edit?id=1
POST /admin/roles/edit?id=1
```

## Data model used

Existing tables are reused:

```text
admin_users
admin_roles
admin_permissions
admin_user_roles
admin_role_permissions
```

## Permission model

- user management requires `user.manage`
- role/permission management requires `role.manage`
- permissions are stored individually and assigned to roles
- users are assigned one or more roles

## Current limitations

- No password reset email flow yet.
- No login history table yet.
- No audit log table yet.
- Super-admin lockout protection should be added before production use.
```
