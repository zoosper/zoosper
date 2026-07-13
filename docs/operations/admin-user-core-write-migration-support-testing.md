# AdminUser core write migration support testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Optional diagnostics:

```bash
php tools/diagnose-user-admin-controller-save-flow.php
```

Expected:

```text
Overall result: OK
```
