# Error notice selector alias hotfix testing

Apply:

```bash
php -l tools/apply-admin-notice-error-selector-alias.php
php tools/apply-admin-notice-error-selector-alias.php
```

Verify:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```
