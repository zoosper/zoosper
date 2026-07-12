# Admin translator container injection testing

Apply and then patch the bootstrap ordering:

```bash
php -l tools/apply-bootstrap-provider-manifest-loader-to-application-factory.php
php tools/apply-bootstrap-provider-manifest-loader-to-application-factory.php
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
