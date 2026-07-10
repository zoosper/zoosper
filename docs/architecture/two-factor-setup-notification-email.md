# Phase 0.47 - 2FA setup notification email

## Goal

Now that real SMTP delivery through Dotdigital is working, Zoosper can send a safe notification email when admins need to complete two-factor authentication setup.

## Security decision

The notification email contains only a message and a link to the secure admin setup page. It must never include:

```text
OTP values
TOTP setup secrets
QR/provisioning URIs
recovery-code plaintext
reset tokens
SMTP passwords
payment data
```

The QR code and setup key stay on the authenticated Zoosper admin setup page only.

## Added

```text
AdminTwoFactorSetupNotificationService
tools/send-2fa-setup-notification.php
tools/verify-2fa-notification-email.php
```

Outbound attempts are logged through the existing SMTP mail log manager.
