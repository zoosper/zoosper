# Phase 0.40 - MySQL 2FA schema finalisation

## Goal

Finalise the database schema needed by admin 2FA reset tooling and the admin user edit reset action.

## Tables

```text
admin_user_two_factor
admin_user_recovery_codes
admin_two_factor_challenges
```

## Design notes

- `admin_user_two_factor.secret_ciphertext` stores encrypted/ protected TOTP secret material.
- `admin_user_recovery_codes.code_hash` stores recovery-code hashes only.
- `admin_two_factor_challenges.challenge_token_hash` stores challenge token hashes only.
- Reset tooling deletes rows by `admin_user_id` so a user can enrol again.

## PCI-aware handling

The schema is designed so runtime code never needs to print or log raw OTPs, TOTP secrets, recovery-code plaintext, provisioning URIs, QR data, SMTP passwords or reset tokens.
