# Phase 0.34 - Wire 2FA Reset and Mail Services

## Source files used

This phase was built from the exported current files in `requested-files-phase-0.34-v2.txt`.

## Replaced files

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
app/zoosper-auth/config/controllers.php
app/zoosper-admin/src/Controller/UserAdminController.php
app/zoosper-two-factor/src/Service/AdminTwoFactorResetService.php
```

## Behaviour

- Registers SMTP services in the shared service container.
- Registers admin 2FA reset repository/service in the shared service container.
- Injects `AdminTwoFactorResetService` into `UserAdminController` through `app/zoosper-auth/config/controllers.php`.
- Adds a Reset 2FA button to the existing admin user edit form.
- Uses the existing `/admin/users/edit?id=<id>` POST flow with `_action=reset_2fa`, avoiding new route assumptions.

## PCI-aware handling

The reset flow does not display, return or log:

```text
OTP values
TOTP secrets
recovery-code plaintext
provisioning URIs
QR data
SMTP passwords
password reset tokens
```

The audit event is best-effort and contains only non-secret user IDs and action metadata.
