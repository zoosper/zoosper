# 2FA setup notification testing

Verify dependencies:

```bash
php tools/verify-2fa-notification-email.php
```

Send a notification to an admin user:

```bash
php tools/send-2fa-setup-notification.php --admin-user-id=1
```

Check the SMTP log grid:

```text
/admin/mail-logs
```

The email should be delivered through the configured SMTP provider and recorded in the log.

## Content policy

The email must not include any sensitive 2FA material. It should only direct the user to sign in and complete setup on the secure admin setup page.
