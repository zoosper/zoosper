# 2FA schema verification

After applying Phase 0.40, run:

```bash
php bin/zoosper migrate
php tools/diagnose-two-factor-schema.php
php tools/verify-two-factor-schema.php
```

Expected result:

```text
admin_user_two_factor: exists
admin_user_recovery_codes: exists
admin_two_factor_challenges: exists
Result: OK
```

Then test the reset CLI:

```bash
php tools/reset-admin-2fa.php --admin-user-id=1 --performed-by=1 --yes
```

The reset action should not print or log any OTPs, TOTP secrets, recovery-code plaintext, provisioning URIs, QR data, SMTP passwords or reset tokens.
