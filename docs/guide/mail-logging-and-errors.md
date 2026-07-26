# Mail, logging & errors

## Outbound mail

Configure SMTP via `.env` (`MAIL_*`, `SMTP_*`). Default local development targets Mailpit on port `1025`:

```bash
docker compose -f deploy/docker/mailpit/docker-compose.mailpit.yml up -d
```

Mailpit's web UI is exposed on http://127.0.0.1:8025.

Test mail configuration:

```bash
php tools/diagnose-mail-config.php
php tools/send-test-email.php --to=admin@example.test
```

Do not commit production SMTP credentials. Do not log SMTP passwords.

Successful SMTP delivery means the remote server accepted the message — not that the recipient’s inbox received it.

## Mail log

The mail module records outbound attempts (recipients, subject, bodies, sent/failed status, error details) for admin review. Because bodies are stored, **do not** send OTPs, recovery codes, or payment data in email content unless a future masking policy protects logged values.

## Module-owned log files

Each module may define `config/logging.php`:

```php
return [
    'file' => 'theme.log',
    // optional: 'service' => 'logger.custom-name',
];
```

Default service id pattern: `logger.<module-name>`.

## Exception logging (two layers)

**Layer 1 — Router:** Uncaught throwables log to `var/log/exception.log` via `ErrorHandler` (with redaction) and return a safe 500 (HTML or JSON for `/api/`).

**Layer 2 — Controllers:** When catching exceptions to show a friendly 422 form, log first:

```php
} catch (RuntimeException $exception) {
    $this->errorHandler?->logException($exception, [
        'controller' => self::class,
        'action' => 'create',
    ]);
    // render validation response
}
```

`LocalLogger` redacts context keys containing `password`, `token`, `secret`, or `session`.

## Related guides

- [Configuration](configuration.md)
- [Security foundations](security-foundations.md)
