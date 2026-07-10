# Phase 0.48 - 2FA login redirect foundation

## Goal

After successful admin login, admins without active 2FA should be redirected to the secure 2FA setup page instead of entering the admin dashboard directly.

## Added

```text
AdminTwoFactorLoginRedirectService
```

The service determines whether the authenticated admin user requires 2FA enrolment and returns either:

```text
/admin/2fa/setup
```

or the configured default admin destination.

## Why this phase is mostly additive

The final redirect needs exact replacement of the current auth controller/session/route files. The latest repository could not be fetched directly in this environment, so this phase adds the reusable redirect service and a file export helper for the next safe full replacement package.

## PCI-aware handling

The redirect decision never exposes or logs OTPs, TOTP secrets, QR/provisioning URIs, recovery-code plaintext, reset tokens, SMTP passwords or payment data.
