# SMTP delivery troubleshooting

## Local Mailpit/MailHog

If diagnostics show:

```text
delivery_mode: local_mail_catcher
```

then emails are captured locally and will not arrive in the real recipient inbox. Open the local catcher UI instead.

## External SMTP

If diagnostics show:

```text
delivery_mode: external_smtp
```

then a successful send means the external SMTP endpoint accepted the message. Downstream delivery still depends on mail routing, SPF/DKIM/DMARC, spam filtering and recipient mailbox systems.

## Commands

```bash
php tools/diagnose-mail-config.php
php tools/send-test-email.php --to=admin@example.test
```
