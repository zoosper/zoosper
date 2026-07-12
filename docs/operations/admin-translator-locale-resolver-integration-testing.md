# Admin translator locale resolver integration testing

Run:

```bash
php -l app/zoosper-core/src/I18n/TranslationResolver.php
php -l app/zoosper-core/src/I18n/AdminTranslatorResolver.php
php -l tools/verify-admin-translator-locale-resolver-integration.php
php -l tools/run-verification-suite.php
php tools/verify-admin-translator-locale-resolver-integration.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```
