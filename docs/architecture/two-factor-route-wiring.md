# Phase 0.43 - 2FA setup route wiring prep

## Goal

Expose the Phase 0.42 admin 2FA setup controller through module-owned admin route declarations and prepare for full login redirect wiring.

## Added route declarations

```text
GET  /admin/2fa/setup
POST /admin/2fa/setup
```

These point to:

```text
Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController::form
Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController::confirm
```

## Why this is additive

The login redirect needs current auth controller, session guard and route loader files. To avoid corrupting working code, this phase adds the module-owned route file and an export helper for the next full replacement phase.

## PCI-aware handling

Routes and controllers must never log OTPs, TOTP secrets, provisioning URIs, QR data, recovery-code plaintext, reset tokens or SMTP passwords.
