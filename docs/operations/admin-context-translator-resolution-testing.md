# Admin context translator resolution testing

Apply and patch:

```bash
php -l tools/apply-admin-context-translator-resolution.php
php tools/apply-admin-context-translator-resolution.php
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

Expected visible behaviour is unchanged unless an admin user has a valid locale and corresponding translation file.
