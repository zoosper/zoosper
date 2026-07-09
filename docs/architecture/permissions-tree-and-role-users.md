# Permission Tree and Role Users

Magento exposes ACL permissions as a tree and also lets admins assign users from the role screen. Zoosper can follow the same UX without copying Magento internals.

## Permission tree proposal

Current permissions are flat strings such as:

```text
page.manage
role.manage
user.manage
settings.manage
```

For Phase 0.9, render them grouped by prefix:

```text
Page
  page.manage
Role
  role.manage
User
  user.manage
```

The next schema improvement should add `parent_code` and `sort_order` to `admin_permissions`, or preferably module-owned `config/acl.php` files that declare a real hierarchy.

## Assign users from role

The Role edit screen can include a searchable user-assignment block. Internally this writes to `admin_user_roles`, the same linking table used by the User edit screen.
