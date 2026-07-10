# Phase 0.34 - Wire 2FA Reset and Mail Services

Next phase should use latest `dev` files and provide full replacements for relevant current files.

Likely files:

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
app/zoosper-admin/src/Controller/AdminUserController.php
app/zoosper-admin/config/routes.php
app/zoosper-admin/config/admin_menu.php
admin user edit/view templates
```

Implementation goals:

- register `SmtpConfig`, `MailerInterface` and `SmtpMailer`
- register `AdminTwoFactorResetRepository` and `AdminTwoFactorResetService`
- add permission-protected admin 2FA reset button/action
- add audit event for reset
- add safe test-mail CLI or admin-only diagnostic later
- never log SMTP password, OTPs, TOTP secrets, recovery-code plaintext or reset tokens
