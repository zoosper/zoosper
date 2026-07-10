# Phase 0.25 - Admin 2FA Foundation

Admin 2FA should be implemented before frontend/customer 2FA.

## PCI-aware requirements

- never log OTP values
- never log TOTP secrets
- never log recovery codes
- never log QR provisioning URLs
- store recovery codes hashed, not in clear text
- protect TOTP secrets with encryption or an equivalent secret-management strategy
- audit enable, disable, verification failure and recovery-code regeneration events

## Scope

Start with admin users only. Frontend/customer 2FA can follow after the admin implementation is stable.
