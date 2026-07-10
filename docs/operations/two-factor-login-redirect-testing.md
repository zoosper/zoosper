# 2FA login redirect testing

Apply this phase, then run:

```bash
php -l app/zoosper-two-factor/src/Service/AdminTwoFactorLoginRedirectService.php
php -l tools/verify-2fa-login-redirect.php
php tools/verify-2fa-login-redirect.php
```

For the next full wiring phase, export the current auth/route files:

```bash
sh tools/export-phase-0.48-files.sh
```

Attach `requested-files-phase-0.48.txt` when requesting full login redirect wiring.

Expected final behaviour after full wiring:

1. Admin logs in successfully.
2. If active 2FA exists, admin goes to the normal admin destination.
3. If active 2FA is missing, admin goes to `/admin/2fa/setup`.
4. Setup page shows QR/manual key and confirms OTP.
5. Sensitive 2FA material never appears in logs or emails.
