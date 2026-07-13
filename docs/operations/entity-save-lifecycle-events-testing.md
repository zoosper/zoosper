# Entity save lifecycle events testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The verifier confirms listener registration, event order, mutation of extension data, and validation-error save blocking.
