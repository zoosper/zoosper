# Phase 0.24 - Admin 2FA Foundation

Planned direction:

- add admin 2FA before frontend/customer 2FA
- support TOTP first
- add recovery codes
- add admin enforcement policy
- audit 2FA enable/disable events
- never log OTPs, recovery codes, secrets or QR setup URLs
- keep PCI compliance in mind by avoiding storage/logging of sensitive authentication secrets in clear text
```
