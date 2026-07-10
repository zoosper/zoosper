# 2FA post-login redirect testing

## Syntax and wiring checks

```bash
php -l app/zoosper-admin/src/Controller/LoginController.php
php -l app/zoosper-admin/config/controllers.php
php -l app/zoosper-two-factor/src/Service/AdminTwoFactorLoginRedirectService.php
php -l tools/verify-2fa-login-post-wiring.php
php tools/verify-2fa-login-post-wiring.php
```

## Browser checks

1. Reset 2FA for an admin user.
2. Log out.
3. Log in as that admin user.
4. Confirm the browser redirects to `/admin/2fa/setup`.
5. Complete setup using the QR code and OTP.
6. Log out and log in again.
7. Confirm the browser redirects to `/admin`.

## Security rule

Do not log, email, print or store OTPs, TOTP secrets, QR/provisioning URIs, recovery-code plaintext, reset tokens, SMTP passwords or payment data.
