# Phase 0.47 progress report

| Feature | Status | Notes |
|---|---|---|
| Real SMTP provider | Working | Dotdigital SMTP accepted test email and inbox received it. |
| SMTP mail log manager | Working | Emails are logged with content/status. |
| 2FA setup QR | Working | QR shows on setup page. |
| 2FA setup notification email | Added | Safe email notification service and CLI sender added. |
| Sensitive 2FA material in email | Blocked by design | Email does not include OTP, secret, QR/provisioning URI or recovery codes. |
| Login redirect to 2FA setup | Pending | Next phase should wire login flow for users without active 2FA. |
| SMTP logs manager enhancements | Future | Masking/retention/export can be added later. |
