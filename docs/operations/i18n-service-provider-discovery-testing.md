# I18n service provider discovery testing

Run:

```bash
php -l tools/apply-i18n-service-provider-discovery.php
php tools/apply-i18n-service-provider-discovery.php
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
