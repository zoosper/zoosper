# I18n service provider registration testing

Run:

```bash
php -l app/zoosper-core/src/I18n/I18nServiceProvider.php
php -l tools/verify-i18n-service-provider-registration.php
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
