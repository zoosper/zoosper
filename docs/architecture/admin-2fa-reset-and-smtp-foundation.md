# Phase 0.33 - Admin 2FA Reset and SMTP Foundation

## Admin 2FA reset

Adds repository/service foundations for resetting a user's admin 2FA state so they can enrol again.

```text
AdminTwoFactorResetRepository
AdminTwoFactorResetService
```

The reset deletes:

```text
admin_user_two_factor
admin_user_recovery_codes
admin_two_factor_challenges
```

## SMTP foundation

Adds a `zoosper-mail` module with a dependency-light SMTP transport foundation:

```text
config/mail.php
Zoosper\Mail\Config\SmtpConfig
Zoosper\Mail\Message\EmailAddress
Zoosper\Mail\Message\EmailMessage
Zoosper\Mail\Transport\MailerInterface
Zoosper\Mail\Transport\SmtpMailer
```

## PCI-aware handling

- SMTP passwords must come from environment variables or secret storage.
- Do not log SMTP passwords, message bodies, password-reset tokens, OTPs, recovery codes, TOTP secrets or provisioning URIs.
- Audit/mail logs should include only non-sensitive metadata such as recipient, template key and outcome.
