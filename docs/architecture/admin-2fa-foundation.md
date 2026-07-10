# Admin 2FA Foundation

Phase 0.25 adds schema and service foundations for admin two-factor authentication.

## Module

```text
app/zoosper-two-factor/
```

## Tables

```text
admin_user_two_factor
admin_user_recovery_codes
admin_two_factor_challenges
```

These tables are defined in module-owned `config/db_schema.php` so `bin/zoosper migrate` can create them through the declarative schema engine.

## PCI-aware handling

- OTP values must never be logged.
- TOTP secrets must never be logged.
- Recovery codes must never be logged.
- Recovery code hashes can be stored; plain recovery codes should be shown once.
- TOTP secrets should be protected before storage.
