# Phase 0.49 - 2FA post-login redirect wiring

## Goal

After a successful admin password login, route admins without active 2FA to the secure setup page before they enter the admin dashboard.

## Behaviour

```text
Successful login + active 2FA exists      -> /admin
Successful login + active 2FA is missing  -> /admin/2fa/setup
Failed login                              -> login form error
```

## Files changed

```text
app/zoosper-admin/src/Controller/LoginController.php
app/zoosper-admin/config/controllers.php
app/zoosper-two-factor/src/Service/AdminTwoFactorLoginRedirectService.php
```

## PCI-aware handling

The login redirect only checks enrolment state. It never exposes or logs OTPs, TOTP secrets, QR/provisioning URIs, recovery-code plaintext, reset tokens, SMTP passwords or payment data.
