# Admin user locale preference testing

Apply schema:

```bash
php -l tools/apply-admin-user-locale-schema.php
php tools/apply-admin-user-locale-schema.php
```

Run verification:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```
