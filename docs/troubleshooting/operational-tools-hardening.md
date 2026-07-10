# Phase 0.35 operational tools hardening

## SMTP test failure

If `tools/send-test-email.php` says the SMTP endpoint is unreachable, the configured SMTP host/port does not have a server listening. The default `.env.example` uses `127.0.0.1:1025`, which expects a local mail catcher or equivalent SMTP test service.

## 2FA reset missing tables

If the active database does not have the 2FA tables yet, run:

```bash
php bin/zoosper migrate
```

Then verify tables with your migration diagnostic tool. The CLI reset now reports missing tables clearly instead of throwing a PDO stack trace.

## PCI-aware note

These tools must never print SMTP passwords, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, provisioning URIs or QR data.
