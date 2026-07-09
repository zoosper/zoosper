# ACL Tree and Role User Assignment

This phase improves the role editor to feel closer to Magento's role editor while remaining Zoosper-native.

## What changed

- `admin_permissions.parent_code` allows permissions to be grouped under a logical parent.
- `admin_permissions.sort_order` controls ordering inside groups.
- `app/zoosper-auth/config/acl.php` defines group labels and order.
- `AclTreeBuilder` renders grouped permissions.
- Role edit can now assign admin users directly to the role.

## Current UX

```text
Role Info
Permission Tree
Assigned Users
```

## Future improvement

Move from grouped fieldsets to a fully nested expandable tree once module-level `config/acl.php` supports recursive children.
