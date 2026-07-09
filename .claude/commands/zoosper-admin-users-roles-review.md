# Zoosper Admin Users/Roles Review

Review this phase for:

1. Password hashes are never displayed.
2. Passwords are always hashed through `PasswordHasher`.
3. User management requires `user.manage`.
4. Role management requires `role.manage`.
5. Role permission matrix updates `admin_role_permissions` only through `RoleRepository`.
6. User-role assignment updates `admin_user_roles` only through `AdminUserRepository`.
7. Every form uses CSRF.
8. Admin layout shows CMS version in the absolute footer.
9. Each module has a translation drop file.
```
