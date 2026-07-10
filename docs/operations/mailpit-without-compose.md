# Mailpit without Docker Compose

Some environments have Docker Engine installed but do not have the Docker Compose v2 plugin or the standalone `docker-compose` binary.

Symptoms:

```text
unknown shorthand flag: 'f' in -f
unknown shorthand flag: 'd' in -d
```

Use plain Docker instead:

```bash
sh tools/start-mailpit-docker.sh
php tools/diagnose-mail-config.php
php tools/send-test-email.php --to=admin@example.test
```

Stop it later:

```bash
sh tools/stop-mailpit-docker.sh
```

Mailpit listens on:

```text
SMTP: 127.0.0.1:1025
UI:   http://127.0.0.1:8025
```

Security note: Mailpit is for local development/testing only. Do not use production SMTP credentials in local test configs and do not log SMTP passwords, OTPs, TOTP secrets, reset tokens or recovery-code plaintext.
