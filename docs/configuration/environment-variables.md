# Environment variables

This document describes environment variables currently represented in `.env.example`.

## Mail / SMTP

The SMTP section is intentionally safe for source control. Real credentials must be provided through local environment variables, deployment secrets or hosting secret storage.

```text
MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=no-reply@example.test
MAIL_FROM_NAME=Zoosper
SMTP_HOST=127.0.0.1
SMTP_PORT=1025
SMTP_USERNAME=
SMTP_PASSWORD=
SMTP_ENCRYPTION=
SMTP_TIMEOUT_SECONDS=15
```

Security rules:

- do not commit real SMTP passwords
- do not log SMTP passwords
- do not log password reset tokens
- do not log OTPs, TOTP secrets, recovery-code plaintext, provisioning URIs or QR data

## Assets

Static assets should live under `/assets/...` rather than application route namespaces such as `/admin/...`.

```text
ASSET_BASE_PATH=/assets
ADMIN_ASSET_PATH=/assets/admin
FRONTEND_ASSET_PATH=/assets/frontend
MODULE_ASSET_PATH=/assets/modules
```

## Admin editor and tag selector

```text
ADMIN_WYSIWYG_ENABLED=false
ADMIN_WYSIWYG_PROVIDER=editorjs
ADMIN_WYSIWYG_STORE_FORMAT=json
ADMIN_TAG_SELECTOR_ENABLED=true
ADMIN_TAG_SELECTOR_MAX_VISIBLE_OPTIONS=25
```
