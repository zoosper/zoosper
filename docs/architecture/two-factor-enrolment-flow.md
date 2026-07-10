# Phase 0.42 - 2FA enrol/re-enrol flow foundation

This phase adds the service/controller foundation for admin TOTP enrolment and re-enrolment after reset.

## Security rules

Do not log or expose after setup:

```text
OTP values
TOTP secrets
QR/provisioning URI
recovery-code plaintext
reset tokens
SMTP passwords
```

Recovery codes are stored as password hashes. TOTP secrets are stored as protected ciphertext.
