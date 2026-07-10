# Phase 0.45 - SMTP mail log manager

## Purpose

Add an operational SMTP email log that records both successful and failed outbound mail attempts.

## What is logged

- recipients
- sender
- subject
- text body
- HTML body
- status: sent/failed
- error class/message for failures
- created/sent/failed timestamps

## Delivery caveat

A successful SMTP send means Zoosper handed the email to the configured SMTP server. It does not guarantee that the recipient saw the email in their inbox. The log is still useful for confirming whether Zoosper attempted the send and whether the SMTP server accepted or rejected the message.

## PCI/security policy

Because this feature stores message content for admin viewing, application code must not send OTPs, TOTP secrets, recovery-code plaintext, QR/provisioning URIs, SMTP passwords, reset tokens or payment data in emails unless a future masking policy protects those values before logging.
