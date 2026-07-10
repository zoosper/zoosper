# Phase 0.39 - CLI bootstrap rollout

## Purpose

Phase 0.38 introduced `tools/bootstrap.php` so CLI tools can load `.env` before reading config. Phase 0.39 applies that bootstrap to operational tools that previously duplicated environment setup.

## Updated tools

```text
tools/assert-production-database.php
tools/diagnose-mail-config.php
tools/send-test-email.php
tools/diagnose-two-factor-schema.php
tools/reset-admin-2fa.php
```

## Security

All tools keep redacting secrets and must not print database passwords, SMTP passwords, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, provisioning URIs or QR data.
