# Reduce manual admin translator fallback testing

Run:

```bash
php -l tools/apply-reduce-admin-translator-fallback.php
php tools/apply-reduce-admin-translator-fallback.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

Browser checks:

```text
/admin/pages
/admin/pages/create
/admin/pages/edit?id=1
/
```

Expected visible behaviour is unchanged.
