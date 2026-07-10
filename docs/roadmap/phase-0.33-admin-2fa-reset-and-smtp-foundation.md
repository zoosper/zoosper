# Phase 0.33 - Admin 2FA Reset and SMTP Foundation

## Admin 2FA reset

Future admin user management should include a permission-protected 2FA reset action that lets authorised admins clear a user's 2FA profile and recovery-code hashes so the user can enrol again.

Security requirements:

- require explicit admin permission
- record an audit event
- do not log TOTP secrets, OTPs, recovery codes, provisioning URIs or QR data
- invalidate existing outstanding 2FA challenge tokens if present

## SMTP foundation

Add a module-owned mailer foundation before password reset and notification features.

Recommended shape:

```text
config/mail.php
Zoosper\Mail\MailerInterface
Zoosper\Mail\SmtpMailer
Zoosper\Mail\EmailMessage
```

Security requirements:

- SMTP password must come from environment/config secret storage
- never log SMTP passwords, reset tokens, OTPs or recovery codes
- support plain text and HTML bodies later
- support audit-safe mail send logs with recipient, template key and outcome only
