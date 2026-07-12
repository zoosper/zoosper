# Supported admin locales testing

Apply config and service registration:

```bash
php -l tools/apply-supported-admin-locales-config.php
php tools/apply-supported-admin-locales-config.php
php -l tools/apply-supported-locale-provider-registration.php
php tools/apply-supported-locale-provider-registration.php
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
