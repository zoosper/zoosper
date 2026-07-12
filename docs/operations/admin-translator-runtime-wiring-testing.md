# Admin translator runtime wiring testing

Run:

```bash
php -l tools/apply-admin-translator-resolver-to-controller.php
php tools/apply-admin-translator-resolver-to-controller.php
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
