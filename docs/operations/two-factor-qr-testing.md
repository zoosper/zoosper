# 2FA QR testing

Apply this phase, then run:

```bash
php -l config/admin.php
php -l app/zoosper-two-factor/src/Qr/TotpQrCodeSvgRenderer.php
php -l app/zoosper-two-factor/src/Controller/AdminTwoFactorSetupController.php
php -l app/zoosper-two-factor/config/controllers.php
php -l tools/add-qr-code-dependency.php
php -l tools/verify-two-factor-qr.php
```

Add the QR dependency if needed:

```bash
php tools/add-qr-code-dependency.php
composer update bacon/bacon-qr-code
composer dump-autoload
php tools/verify-two-factor-qr.php
```

Then open:

```text
/admin/2fa/setup
```

Expected:

- QR code is shown if the dependency is installed.
- Manual setup key remains available as a fallback.
- Authenticator URI is hidden inside a details block.

Security note: never send `otpauth://` URIs to external QR services.
