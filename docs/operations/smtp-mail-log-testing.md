# SMTP mail log testing

Apply this phase, then run:

```bash
php tools/wire-smtp-mail-logger.php
composer dump-autoload
php bin/zoosper migrate
php tools/verify-smtp-email-log-schema.php
php tools/send-test-email.php --to=admin@example.test
```

Open the admin grid:

```text
/admin/mail-logs
```

Search/filter by:

- status
- email
- subject

Click **View** to inspect email content and send status.
