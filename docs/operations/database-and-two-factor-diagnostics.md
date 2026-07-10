# Database and 2FA diagnostics

Phase 0.36 adds read-only CLI diagnostics to make environment mismatches obvious.

## Database connection

```bash
php tools/diagnose-database-connection.php
```

This prints the active CLI PDO driver and safe database identity details without printing passwords or full DSNs.

## 2FA schema

```bash
php tools/diagnose-two-factor-schema.php
```

This checks for:

```text
admin_user_two_factor
admin_user_recovery_codes
admin_two_factor_challenges
```

If the active CLI database is missing these tables, run:

```bash
php bin/zoosper migrate
```

and then re-run the diagnostic.

## PCI-aware handling

The tools only print table presence and row counts. They do not read or print TOTP secrets, OTPs, recovery-code plaintext, provisioning URIs, QR data, reset tokens or SMTP passwords.
