# Phase 0.46 - SMTP delivery semantics

## Problem

A message can be logged as `sent` when the configured SMTP endpoint accepts it, but the recipient may still not receive the email. This is especially expected when SMTP points to a local catcher such as Mailpit on `127.0.0.1:1025`.

## Clarification

`sent` means:

```text
Zoosper handed the message to the configured SMTP endpoint without SMTP transport error.
```

It does not mean:

```text
The recipient saw the message in their real inbox.
```

## Added

- `SmtpDeliveryModeInspector`
- updated `diagnose-mail-config.php`
- updated `send-test-email.php`
- admin mail log UI notices explaining accepted-vs-delivered semantics

## PCI-aware handling

Diagnostics never print SMTP passwords, OTPs, TOTP secrets, recovery-code plaintext, provisioning URIs, QR data or reset tokens.
