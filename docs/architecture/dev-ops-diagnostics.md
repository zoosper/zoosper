# Phase 0.36 - Dev operations diagnostics

## Purpose

Phase 0.36 adds small, safe tools for diagnosing common local development issues without exposing secrets.

## Included

```text
tools/diagnose-database-connection.php
tools/diagnose-two-factor-schema.php
deploy/docker/mailpit/docker-compose.mailpit.yml
docs/operations/local-smtp-mailpit.md
docs/operations/database-and-two-factor-diagnostics.md
```

## Why

The hardened Phase 0.35 tools correctly reported:

- SMTP was configured but no SMTP endpoint was reachable.
- The active CLI database did not have 2FA tables.

Phase 0.36 makes both issues easier to diagnose and reproduce.
