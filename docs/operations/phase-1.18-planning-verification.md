# Phase 1.18 planning verification

Run:

```bash
php -l tools/verify-roadmap-planning-docs.php
php tools/verify-roadmap-planning-docs.php
```

Expected:

```text
Result: OK
```

The verifier checks that restored roadmap TODOs, SQL placeholder guidance, admin notice guidance and extension data persistence planning are present.
