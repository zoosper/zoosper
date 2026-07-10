# SMTP email log schema error: unsupported longtext

## Symptom

```text
Unsupported declarative schema column type: longtext
```

## Cause

The current Zoosper declarative schema engine does not support the `longtext` type. Phase 0.45 originally used `longtext` for `text_body` and `html_body`, so `php bin/zoosper migrate` stopped before creating `smtp_email_log`.

## Fix

This hotfix changes:

```text
text_body: longtext -> text
html_body: longtext -> text
```

This keeps migrations compatible with the current schema engine.

## Future option

A later schema-engine phase can add native `longtext` support if email body retention needs larger storage. For now, `text` is enough to unblock migrations and the SMTP log grid.

## PCI-aware rule

Do not store OTP values, TOTP secrets, recovery-code plaintext, provisioning URIs, reset tokens, SMTP passwords, payment data or other sensitive values in logged email content unless a future masking policy explicitly protects those values before storage.
