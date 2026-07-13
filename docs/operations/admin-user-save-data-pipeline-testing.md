# AdminUser save data pipeline testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The verifier confirms locale is normalised, unsafe/blank locale values become null, rogue values do not reach core writes, and third-party extension data remains available.
