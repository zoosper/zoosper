# Audit Log and Login History

Phase 0.9 adds two security tables:

```text
admin_login_history
admin_activity_log
```

## Login history records

The admin login controller records:

- successful login
- failed login
- CSRF failure during login

Each row stores email, optional admin user ID, status, IP address, user agent and timestamp.

## Audit log records

`AuditLogger` provides one service for recording who changed what:

```php
$auditLogger->record(
    actor: $adminUser,
    action: 'role.updated',
    entityType: 'admin_role',
    entityId: (string) $roleId,
    summary: 'Updated role permissions',
    metadata: ['permission_ids' => [1, 2, 3]],
    request: $request,
);
```

## Next integration points

- record admin user create/update
- record role create/update
- record page publish/unpublish
- record module enable/disable when module admin exists
