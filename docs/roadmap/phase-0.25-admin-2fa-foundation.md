# Phase 0.25 - Admin 2FA Foundation

Admin 2FA should be implemented before frontend/customer 2FA.

## Direction

- TOTP first
- recovery codes
- enforced 2FA policy for admin roles
- audit events for enable, disable, recovery-code regeneration and challenge success/failure
- admin login flow should require second factor after password validation

## PCI-aware handling

- never log OTPs
- never log recovery codes
- never log TOTP secrets
- never expose QR setup URLs in logs
- encrypt or otherwise protect stored 2FA secrets
- hash recovery codes rather than storing them in clear text
