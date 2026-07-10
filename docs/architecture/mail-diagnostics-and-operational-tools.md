# Phase 0.35 - Mail diagnostics and operational tools

## Purpose

Phase 0.35 adds safe operational tooling for the SMTP and 2FA reset foundations introduced in Phase 0.33/0.34.

## Includes

```text
MailConfigurationSummary
MailConfigurationInspector
tools/diagnose-mail-config.php
tools/send-test-email.php
tools/reset-admin-2fa.php
```

## PCI-aware handling

The diagnostics intentionally redact sensitive values. The tools must not print or log:

```text
SMTP passwords
message bodies in logs
password reset tokens
OTP values
TOTP secrets
recovery-code plaintext
provisioning URIs
QR data
```

## CLI tools

```bash
php tools/diagnose-mail-config.php
php tools/send-test-email.php --to=admin@example.test
php tools/reset-admin-2fa.php --admin-user-id=1 --performed-by=1 --yes
```
