# Bootstrap provider manifest runtime wiring testing

Apply and verify:

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

If anything fails, attach:

```text
var/reports/latest-verification.txt
```
