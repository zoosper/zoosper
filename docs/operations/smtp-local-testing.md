# Local SMTP testing

For local development, the `.env.example` default uses:

```text
SMTP_HOST=127.0.0.1
SMTP_PORT=1025
```

This is suitable for a local mail catcher. Use:

```bash
php tools/diagnose-mail-config.php
php tools/send-test-email.php --to=admin@example.test
```

Do not use real production SMTP credentials in `.env.example` or commit them to source control.
