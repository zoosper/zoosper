# AdminUser save context extension provider hotfix testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The verifier should now prove that extension field data remains available when extension providers are supplied to the AdminUser registry factory.
