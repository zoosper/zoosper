# Roadmap and error CSS verification hotfix testing

Apply:

```bash
php -l tools/apply-roadmap-and-error-css-verification-hotfix.php
php tools/apply-roadmap-and-error-css-verification-hotfix.php
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
