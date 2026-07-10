# 2FA route testing

Apply this phase, then run:

```bash
php -l app/zoosper-two-factor/config/admin_routes.php
php -l tools/verify-two-factor-enrolment-foundation.php
php tools/verify-two-factor-enrolment-foundation.php
```

If your route loader already reads module-owned `config/admin_routes.php`, test:

```text
/admin/2fa/setup
```

If the route is not found, export the latest route/auth files:

```bash
sh tools/export-phase-0.43-files.sh
```

Attach `requested-files-phase-0.43.txt` and request the full route/login redirect replacement package.
