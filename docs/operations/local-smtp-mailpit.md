# Local SMTP testing with Mailpit

Zoosper's default development SMTP settings use:

```text
SMTP_HOST=127.0.0.1
SMTP_PORT=1025
```

Phase 0.36 includes a small Docker Compose file for Mailpit:

```text
deploy/docker/mailpit/docker-compose.mailpit.yml
```

Start it from the repository root:

```bash
docker compose -f deploy/docker/mailpit/docker-compose.mailpit.yml up -d
```

Then run:

```bash
php tools/diagnose-mail-config.php
php tools/send-test-email.php --to=admin@example.test
```

Mailpit's web UI is exposed on:

```text
http://127.0.0.1:8025
```

## Security rules

- Do not use production SMTP credentials in local test configs.
- Do not log SMTP passwords.
- Do not log password reset tokens, OTPs, TOTP secrets, recovery-code plaintext, provisioning URIs or QR data.
