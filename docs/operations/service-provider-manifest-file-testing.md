# Service provider manifest file testing

Run:

```bash
php -l config/service_providers.php
php -l tools/verify-service-provider-manifest-file.php
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
