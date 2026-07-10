# Admin 2FA Enrolment Foundation

Phase 0.26 adds service-level foundations for admin TOTP enrolment.

## Included services

```text
TotpVerifier
TotpProvisioningUriGenerator
AdminTwoFactorEnrolmentService
AdminRecoveryCodeRepository
```

## PCI-aware handling

- OTP values are accepted for verification only and must never be logged.
- TOTP secrets are generated for setup and protected before persistence.
- Provisioning URIs contain the secret and must not be logged.
- Recovery codes are returned once for display and only hashes are persisted.

## Not yet included

This phase does not wire controllers/routes/templates because those are existing files and should be updated only after fetching the latest code from the `dev` branch or attaching the latest files.
