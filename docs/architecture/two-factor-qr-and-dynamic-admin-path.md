# Phase 0.44 - 2FA QR setup and dynamic admin path foundation

## Goal

Improve the 2FA setup UX by showing a local QR code and start removing hard-coded `/admin` URLs from controller-generated links.

## QR code policy

The QR code contains the `otpauth://` provisioning URI, which includes the TOTP secret. It must be rendered locally only. Do not use external QR code APIs or external image services.

## Dynamic admin path

`config/admin.php` introduces:

```text
ADMIN_BASE_PATH=/admin
```

The 2FA setup controller now builds form/action links from this config instead of hard-coding `/admin` internally.

Route declarations still use current route paths until the route loader itself is made base-path aware in a later phase.
